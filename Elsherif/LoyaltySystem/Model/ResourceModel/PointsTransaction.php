<?php
/**
 * Points Transaction Resource Model
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PointsTransaction extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('elsherif_points_transaction', 'transaction_id');
    }
}
