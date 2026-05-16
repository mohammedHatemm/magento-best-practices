<?php
/**
 * Expire Points Cron Job
 * Runs daily to expire old points
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Cron;

use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction\CollectionFactory;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Psr\Log\LoggerInterface;

class ExpirePoints
{
    private $transactionCollectionFactory;
    private $pointsManagement;
    private $config;
    private $logger;

    public function __construct(
        CollectionFactory $transactionCollectionFactory,
        PointsManagementInterface $pointsManagement,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $this->logger->info('Loyalty: Starting points expiration job');

        try {
            // Get expired transactions
            $collection = $this->transactionCollectionFactory->create();
            $collection->filterExpired();

            $expiredCount = 0;
            $totalPoints = 0;

            foreach ($collection as $transaction) {
                $points = abs($transaction->getPoints());

                // Deduct expired points
                $this->pointsManagement->deductPoints(
                    $transaction->getCustomerId(),
                    $points,
                    'expired',
                    $transaction->getTransactionId(),
                    "Points expired from transaction #{$transaction->getTransactionId()}"
                );

                $expiredCount++;
                $totalPoints += $points;
            }

            $this->logger->info("Loyalty: Expired {$totalPoints} points from {$expiredCount} transactions");

        } catch (\Exception $e) {
            $this->logger->error('Loyalty expiration error: ' . $e->getMessage());
        }
    }
}
