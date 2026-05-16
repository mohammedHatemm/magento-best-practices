<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Order;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Elsherif\LoyaltySystem\Helper\Data as DataHelper;
use Psr\Log\LoggerInterface;

/**
 * Plugin to earn loyalty points when order is placed
 */
class EarnPointsPlugin
{
    /**
     * @var PointsManagementInterface
     */
    private PointsManagementInterface $pointsManagement;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var DataHelper
     */
    private DataHelper $dataHelper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PointsManagementInterface $pointsManagement
     * @param Config $config
     * @param DataHelper $dataHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        PointsManagementInterface $pointsManagement,
        Config $config,
        DataHelper $dataHelper,
        LoggerInterface $logger
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    /**
     * Earn points after order is placed
     *
     * @param OrderManagementInterface $subject
     * @param OrderInterface $result
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result,
        OrderInterface $order
    ): OrderInterface {
        try {
            // Check if module is enabled
            if (!$this->config->isEnabled()) {
                return $result;
            }

            // Must have customer
            $customerId = (int) $result->getCustomerId();
            if (!$customerId) {
                return $result;
            }

            // Calculate points from order items
            $totalPoints = $this->calculateOrderPoints($result);

            if ($totalPoints <= 0) {
                return $result;
            }

            // Add points to customer
            $expiresAt = $this->dataHelper->getExpirationDate();

            $this->pointsManagement->addPoints(
                $customerId,
                $totalPoints,
                'order_complete',
                (int) $result->getEntityId(),
                $expiresAt,
                "Earned from order #{$result->getIncrementId()}"
            );

            // Update order extension attributes
            $result->setData('loyalty_points_earned', $totalPoints);

            $this->logger->info(
                "Loyalty: Added {$totalPoints} points to customer {$customerId} for order #{$result->getIncrementId()}"
            );

        } catch (\Exception $e) {
            $this->logger->error('Loyalty Plugin Error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Calculate total points from order items
     *
     * @param OrderInterface $order
     * @return int
     */
    private function calculateOrderPoints(OrderInterface $order): int
    {
        $totalPoints = 0;

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            
            // Get points from product attribute
            $productPoints = 0;
            if ($product) {
                $productPoints = (int) $product->getData('loyalty_points');
            }

            // If no product points set, use default calculation
            if ($productPoints <= 0) {
                $productPoints = $this->calculateDefaultPoints($item);
            }

            // Multiply by quantity
            $itemPoints = $productPoints * (int) $item->getQtyOrdered();
            $totalPoints += $itemPoints;
        }

        return $totalPoints;
    }

    /**
     * Calculate default points based on price
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return int
     */
    private function calculateDefaultPoints($item): int
    {
        $earnRate = $this->config->getEarnRate();
        $rowTotal = (float) $item->getRowTotal();
        
        // Subtract discount if applicable
        $rowTotal -= (float) $item->getDiscountAmount();
        
        if ($rowTotal <= 0) {
            return 0;
        }

        // Calculate: price / earnRate = points
        // e.g., $100 / 10 = 10 points (earn 1 point per $10)
        return (int) floor($rowTotal / $earnRate);
    }
}
