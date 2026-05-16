<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Refund points when order is refunded
 */
class RefundPointsObserver implements ObserverInterface
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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PointsManagementInterface $pointsManagement
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        PointsManagementInterface $pointsManagement,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            if (!$this->config->isEnabled()) {
                return;
            }

            /** @var Creditmemo $creditmemo */
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $order = $creditmemo->getOrder();

            $customerId = (int) $order->getCustomerId();
            if (!$customerId) {
                return;
            }

            // Calculate points to deduct based on refund
            $pointsToDeduct = $this->calculateRefundPoints($creditmemo);

            if ($pointsToDeduct <= 0) {
                return;
            }

            // Deduct points (negative value)
            $this->pointsManagement->addPoints(
                $customerId,
                -$pointsToDeduct,
                'refund',
                (int) $order->getEntityId(),
                null,
                "Points deducted for refund on order #{$order->getIncrementId()}"
            );

            $this->logger->info(
                "Loyalty: Deducted {$pointsToDeduct} points from customer {$customerId} for refund"
            );

        } catch (\Exception $e) {
            $this->logger->error('Loyalty Refund Error: ' . $e->getMessage());
        }
    }

    /**
     * Calculate points to deduct for refund
     *
     * @param Creditmemo $creditmemo
     * @return int
     */
    private function calculateRefundPoints(Creditmemo $creditmemo): int
    {
        $totalPoints = 0;

        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if (!$orderItem) {
                continue;
            }

            $product = $orderItem->getProduct();
            
            // Get points from product attribute
            $productPoints = 0;
            if ($product) {
                $productPoints = (int) $product->getData('loyalty_points');
            }

            // If no product points set, use default calculation
            if ($productPoints <= 0) {
                $earnRate = $this->config->getEarnRate();
                $rowTotal = (float) $item->getRowTotal();
                $productPoints = (int) floor($rowTotal / $earnRate);
            }

            // Multiply by quantity
            $itemPoints = $productPoints * (int) $item->getQty();
            $totalPoints += $itemPoints;
        }

        return $totalPoints;
    }
}
