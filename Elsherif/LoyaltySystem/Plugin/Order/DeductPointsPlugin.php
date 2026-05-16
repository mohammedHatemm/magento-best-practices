<?php
/**
 * Plugin to deduct loyalty points when order is placed
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Order;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Psr\Log\LoggerInterface;

class DeductPointsPlugin
{
    private PointsManagementInterface $pointsManagement;
    private CartRepositoryInterface $quoteRepository;
    private Config $config;
    private LoggerInterface $logger;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        CartRepositoryInterface $quoteRepository,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Deduct loyalty points after order is placed
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result,
        OrderInterface $order
    ): OrderInterface {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        try {
            $customerId = (int) $result->getCustomerId();
            if (!$customerId) {
                return $result;
            }

            // Get loyalty data from order or quote
            $pointsUsed = (int) $result->getData('loyalty_points_used');
            $discountAmount = (float) $result->getData('loyalty_discount_amount');

            // If not on order, try quote
            if ($pointsUsed <= 0) {
                $quoteId = $result->getQuoteId();
                if ($quoteId) {
                    try {
                        $quote = $this->quoteRepository->get($quoteId);
                        $pointsUsed = (int) $quote->getData('loyalty_points_used');
                        $discountAmount = (float) $quote->getData('loyalty_discount_amount');
                    } catch (\Exception $e) {
                        $this->logger->debug('Loyalty: Could not load quote - ' . $e->getMessage());
                    }
                }
            }

            if ($pointsUsed <= 0) {
                return $result;
            }

            // Deduct points from customer balance
            $this->pointsManagement->deductPoints(
                $customerId,
                $pointsUsed,
                'redemption',
                (int) $result->getEntityId(),
                "Redeemed on order #{$result->getIncrementId()}"
            );

            // Ensure data is saved to order
            $result->setData('loyalty_points_used', $pointsUsed);
            $result->setData('loyalty_discount_amount', $discountAmount);

            $this->logger->info(
                "Loyalty: Deducted {$pointsUsed} points from customer {$customerId} for order #{$result->getIncrementId()}"
            );

        } catch (\Exception $e) {
            $this->logger->error('Loyalty DeductPoints Plugin Error: ' . $e->getMessage());
        }

        return $result;
    }
}
