<?php
/**
 * Earn Points When Order is Complete
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Elsherif\LoyaltySystem\Model\PointsCalculator;
use Elsherif\LoyaltySystem\Helper\Data as DataHelper;
use Elsherif\LoyaltySystem\Helper\Email as EmailHelper;
use Psr\Log\LoggerInterface;

class EarnPointsOnOrderObserver implements ObserverInterface
{
    private $pointsManagement;
    private $config;
    private $calculator;
    private $dataHelper;
    private $emailHelper;
    private $logger;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        Config $config,
        PointsCalculator $calculator,
        DataHelper $dataHelper,
        EmailHelper $emailHelper,
        LoggerInterface $logger
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->calculator = $calculator;
        $this->dataHelper = $dataHelper;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            // Validate
            if (!$this->isEligible($order)) {
                return;
            }

            // Calculate points
            $points = $this->calculator->calculateEarnedPoints($order);

            if ($points <= 0) {
                return;
            }

            // Add points
            $expiresAt = $this->dataHelper->getExpirationDate();
            
            $this->pointsManagement->addPoints(
                (int) $order->getCustomerId(),
                $points,
                'order_complete',
                (int) $order->getId(),
                $expiresAt,
                "Earned from order #{$order->getIncrementId()}"
            );

            // Save to order extension attributes
            $extensionAttributes = $order->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributes->setLoyaltyPointsEarned($points);
                $order->setExtensionAttributes($extensionAttributes);
            }

            // Send email
            $balance = $this->pointsManagement->getBalance((int) $order->getCustomerId());
            
            $this->emailHelper->sendPointsEarnedEmail(
                $order->getCustomerEmail(),
                $order->getCustomerName(),
                $points,
                $balance->getPoints()
            );

            $this->logger->info("Loyalty: Added {$points} points to customer {$order->getCustomerId()}");

        } catch (\Exception $e) {
            $this->logger->error('Error earning points: ' . $e->getMessage());
        }
    }

    private function isEligible(Order $order): bool
    {
        // Check if module enabled
        if (!$this->config->isEnabled()) {
            return false;
        }

        // Must have customer
        if (!$order->getCustomerId()) {
            return false;
        }

        // Check order state - earn points on new, processing, or complete
        $allowedStates = [
            Order::STATE_NEW,
            Order::STATE_PROCESSING,
            Order::STATE_COMPLETE
        ];
        if (!in_array($order->getState(), $allowedStates)) {
            return false;
        }

        // Don't earn twice
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getLoyaltyPointsEarned()) {
            return false;
        }

        return true;
    }
}
