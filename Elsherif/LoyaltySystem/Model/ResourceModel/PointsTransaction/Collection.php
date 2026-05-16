<?php
/**
 * Points Transaction Collection
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Elsherif\LoyaltySystem\Model\PointsTransaction;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction as PointsTransactionResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            PointsTransaction::class,
            PointsTransactionResource::class
        );
    }

    /**
     * Filter by customer
     *
     * @param int $customerId
     * @return $this
     */
    public function filterByCustomer(int $customerId): self
    {
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    /**
     * Filter by action
     *
     * @param string $action
     * @return $this
     */
    public function filterByAction(string $action): self
    {
        $this->addFieldToFilter('action', $action);
        return $this;
    }

    /**
     * Filter expired transactions
     *
     * @return $this
     */
    public function filterExpired(): self
    {
        $this->addFieldToFilter('expires_at', [
            'notnull' => true
        ]);
        $this->addFieldToFilter('expires_at', [
            'lt' => new \Zend_Db_Expr('NOW()')
        ]);
        return $this;
    }

    /**
     * Order by creation date
     *
     * @param string $dir
     * @return $this
     */
    public function orderByCreatedAt(string $dir = 'DESC'): self
    {
        $this->setOrder('created_at', $dir);
        return $this;
    }
}
