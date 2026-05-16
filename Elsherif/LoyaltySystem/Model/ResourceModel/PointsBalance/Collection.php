<?php
/**
 * Points Balance Collection
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Elsherif\LoyaltySystem\Model\PointsBalance;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance as PointsBalanceResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'balance_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            PointsBalance::class,
            PointsBalanceResource::class
        );
    }
}
