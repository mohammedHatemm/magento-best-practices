<?php
/**
 * Points Balance Resource Model
 * Database operations
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PointsBalance extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('elsherif_points_balance', 'balance_id');
    }

    /**
     * Load balance by customer ID
     *
     * @param \Elsherif\LoyaltySystem\Model\PointsBalance $object
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomerId($object, int $customerId)
    {
        $connection = $this->getConnection();
        $bind = ['customer_id' => $customerId];
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('customer_id = :customer_id');

        $data = $connection->fetchRow($select, $bind);

        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }
}
