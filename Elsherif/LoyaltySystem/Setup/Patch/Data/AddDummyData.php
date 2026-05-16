<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Add dummy data for testing the Loyalty System
 */
class AddDummyData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        // Get first customer ID
        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize(1)
            ->create();
        
        $customers = $this->customerRepository->getList($searchCriteria);
        $customerItems = $customers->getItems();
        
        if (empty($customerItems)) {
            $this->moduleDataSetup->endSetup();
            return $this;
        }

        $customer = reset($customerItems);
        $customerId = (int) $customer->getId();

        $connection = $this->moduleDataSetup->getConnection();
        $balanceTable = $this->moduleDataSetup->getTable('elsherif_points_balance');
        $transactionTable = $this->moduleDataSetup->getTable('elsherif_points_transaction');

        // Check if data already exists
        $select = $connection->select()
            ->from($balanceTable)
            ->where('customer_id = ?', $customerId);
        
        if ($connection->fetchOne($select)) {
            $this->moduleDataSetup->endSetup();
            return $this;
        }

        // Insert Points Balance
        $connection->insert($balanceTable, [
            'customer_id' => $customerId,
            'points' => 1500,
            'lifetime_points' => 2500,
            'points_spent' => 1000
        ]);

        // Insert Transaction History
        $transactions = [
            [
                'customer_id' => $customerId,
                'points' => 500,
                'balance_after' => 500,
                'action' => 'order_complete',
                'reference_id' => 100000001,
                'reference_type' => 'order',
                'comment' => 'Points earned from Order #100000001',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ],
            [
                'customer_id' => $customerId,
                'points' => 750,
                'balance_after' => 1250,
                'action' => 'order_complete',
                'reference_id' => 100000002,
                'reference_type' => 'order',
                'comment' => 'Points earned from Order #100000002',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days'))
            ],
            [
                'customer_id' => $customerId,
                'points' => -500,
                'balance_after' => 750,
                'action' => 'redemption',
                'reference_id' => 100000003,
                'reference_type' => 'order',
                'comment' => 'Points redeemed on Order #100000003',
                'expires_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
            ],
            [
                'customer_id' => $customerId,
                'points' => 1000,
                'balance_after' => 1750,
                'action' => 'order_complete',
                'reference_id' => 100000004,
                'reference_type' => 'order',
                'comment' => 'Points earned from Order #100000004',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
            ],
            [
                'customer_id' => $customerId,
                'points' => 250,
                'balance_after' => 2000,
                'action' => 'admin_adjust',
                'reference_id' => null,
                'reference_type' => 'manual',
                'comment' => 'Bonus points - Welcome reward',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+6 months')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'customer_id' => $customerId,
                'points' => -500,
                'balance_after' => 1500,
                'action' => 'redemption',
                'reference_id' => 100000005,
                'reference_type' => 'order',
                'comment' => 'Points redeemed on Order #100000005',
                'expires_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ]
        ];

        foreach ($transactions as $transaction) {
            $connection->insert($transactionTable, $transaction);
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            CreateDummyCustomers::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
