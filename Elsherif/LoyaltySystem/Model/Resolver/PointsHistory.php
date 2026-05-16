<?php
/**
 * GraphQL Resolver for Customer Points Transaction History
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction\CollectionFactory;
use Elsherif\LoyaltySystem\Helper\Config;

class PointsHistory implements ResolverInterface
{
    private CollectionFactory $collectionFactory;
    private Config $config;

    public function __construct(
        CollectionFactory $collectionFactory,
        Config $config
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        if (!$this->config->isEnabled()) {
            return [
                'items' => [],
                'page_info' => [
                    'page_size' => 0,
                    'current_page' => 1,
                    'total_pages' => 0
                ],
                'total_count' => 0
            ];
        }

        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('Please login to view points history.'));
        }

        $customerId = $context->getUserId();
        $pageSize = (int) ($args['pageSize'] ?? 10);
        $currentPage = (int) ($args['currentPage'] ?? 1);

        // Get total count
        $countCollection = $this->collectionFactory->create();
        $countCollection->addFieldToFilter('customer_id', $customerId);
        $totalCount = $countCollection->getSize();

        // Get paginated items
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->setOrder('created_at', 'DESC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($currentPage);

        $items = [];
        foreach ($collection as $transaction) {
            $items[] = [
                'transaction_id' => (int) $transaction->getTransactionId(),
                'action' => $transaction->getAction(),
                'points' => (int) $transaction->getPoints(),
                'balance_after' => (int) $transaction->getBalanceAfter(),
                'reference_id' => $transaction->getReferenceId() ? (int) $transaction->getReferenceId() : null,
                'comment' => $transaction->getComment(),
                'created_at' => $transaction->getCreatedAt(),
                'expires_at' => $transaction->getExpiresAt()
            ];
        }

        $totalPages = $pageSize > 0 ? (int) ceil($totalCount / $pageSize) : 0;

        return [
            'items' => $items,
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => $totalPages
            ],
            'total_count' => $totalCount
        ];
    }
}
