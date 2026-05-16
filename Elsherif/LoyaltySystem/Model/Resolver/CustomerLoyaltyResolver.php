<?php
/**
 * GraphQL Resolver for Customer Loyalty Data
 * Used when accessing customer.loyalty_points
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Helper\Config;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction\CollectionFactory as TransactionCollectionFactory;

class CustomerLoyaltyResolver implements ResolverInterface
{
    private PointsManagementInterface $pointsManagement;
    private Config $config;
    private TransactionCollectionFactory $transactionCollectionFactory;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        Config $config,
        TransactionCollectionFactory $transactionCollectionFactory
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): ?array {
        if (!$this->config->isEnabled()) {
            return null;
        }

        $customerId = $value['model']->getId() ?? null;
        
        if (!$customerId) {
            return null;
        }

        $balance = $this->pointsManagement->getBalance((int) $customerId);
        
        // Get pending points (from orders not yet complete)
        $pendingPoints = $this->getPendingPoints((int) $customerId);
        
        // Get next expiry info
        $nextExpiry = $this->getNextExpiry((int) $customerId);

        return [
            'balance_id' => $balance->getBalanceId(),
            'customer_id' => $balance->getCustomerId(),
            'points' => $balance->getPoints(),
            'lifetime_points' => $balance->getLifetimePoints(),
            'points_spent' => $balance->getPointsSpent(),
            'points_pending' => $pendingPoints,
            'tier' => $this->getCustomerTier($balance->getLifetimePoints()),
            'next_expiry' => $nextExpiry,
            'updated_at' => $balance->getUpdatedAt()
        ];
    }

    private function getPendingPoints(int $customerId): int
    {
        $collection = $this->transactionCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('action', 'pending');
        
        $total = 0;
        foreach ($collection as $transaction) {
            $total += $transaction->getPoints();
        }
        
        return $total;
    }

    private function getNextExpiry(int $customerId): ?array
    {
        $collection = $this->transactionCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('expires_at', ['notnull' => true]);
        $collection->addFieldToFilter('expires_at', ['gt' => date('Y-m-d H:i:s')]);
        $collection->setOrder('expires_at', 'ASC');
        $collection->setPageSize(1);
        
        $transaction = $collection->getFirstItem();
        
        if ($transaction->getId()) {
            return [
                'points' => (int) $transaction->getPoints(),
                'expiry_date' => $transaction->getExpiresAt()
            ];
        }
        
        return null;
    }

    private function getCustomerTier(int $lifetimePoints): array
    {
        // Define tiers - can be moved to config
        $tiers = [
            ['code' => 'platinum', 'name' => 'Platinum', 'min_points' => 10000, 'benefits' => ['20% bonus points', 'Free shipping', 'Exclusive offers']],
            ['code' => 'gold', 'name' => 'Gold', 'min_points' => 5000, 'benefits' => ['15% bonus points', 'Priority support']],
            ['code' => 'silver', 'name' => 'Silver', 'min_points' => 1000, 'benefits' => ['10% bonus points']],
            ['code' => 'bronze', 'name' => 'Bronze', 'min_points' => 0, 'benefits' => ['Standard earning rate']]
        ];

        foreach ($tiers as $tier) {
            if ($lifetimePoints >= $tier['min_points']) {
                return $tier;
            }
        }

        return $tiers[array_key_last($tiers)];
    }
}
