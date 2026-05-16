<?php
/**
 * Deduct Points When Order is Placed
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Psr\Log\LoggerInterface;

class DeductPointsOnOrderObserver implements ObserverInterface
{
    private $pointsManagement;
    private $logger;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        LoggerInterface $logger
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            if (!$order->getCustomerId()) {
                return;
            }

            // Get points used from quote
            $quote = $order->getQuote();
            if (!$quote) {
                return;
            }

            $extensionAttributes = $quote->getExtensionAttributes();
            if (!$extensionAttributes) {
                return;
            }

            $pointsUsed = $extensionAttributes->getLoyaltyPointsUsed();
            if (!$pointsUsed || $pointsUsed <= 0) {
                return;
            }

            // Deduct points
            $this->pointsManagement->deductPoints(
                (int) $order->getCustomerId(),
                $pointsUsed,
                'redemption',
                (int) $order->getId(),
                "Redeemed on order #{$order->getIncrementId()}"
            );

            // Save to order
            $orderExtension = $order->getExtensionAttributes();
            if ($orderExtension) {
                $orderExtension->setLoyaltyPointsUsed($pointsUsed);
                $orderExtension->setLoyaltyDiscountAmount(
                    $extensionAttributes->getLoyaltyDiscountAmount()
                );
                $order->setExtensionAttributes($orderExtension);
            }

            $this->logger->info("Loyalty: Deducted {$pointsUsed} points from customer {$order->getCustomerId()}");

        } catch (\Exception $e) {
            $this->logger->error('Error deducting points: ' . $e->getMessage());
        }
    }
}
