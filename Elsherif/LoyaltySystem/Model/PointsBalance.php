<?php
/**
 * Points Balance Model
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Magento\Framework\Model\AbstractModel;
use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance as PointsBalanceResource;

class PointsBalance extends AbstractModel implements PointsBalanceInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'elsherif_loyalty_points_balance';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PointsBalanceResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getBalanceId(): ?int
    {
        return $this->getData(self::BALANCE_ID) 
            ? (int) $this->getData(self::BALANCE_ID) 
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setBalanceId(int $balanceId): PointsBalanceInterface
    {
        return $this->setData(self::BALANCE_ID, $balanceId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): PointsBalanceInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getPoints(): int
    {
        return (int) $this->getData(self::POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setPoints(int $points): PointsBalanceInterface
    {
        return $this->setData(self::POINTS, $points);
    }

    /**
     * @inheritDoc
     */
    public function getLifetimePoints(): int
    {
        return (int) $this->getData(self::LIFETIME_POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setLifetimePoints(int $lifetimePoints): PointsBalanceInterface
    {
        return $this->setData(self::LIFETIME_POINTS, $lifetimePoints);
    }

    /**
     * @inheritDoc
     */
    public function getPointsSpent(): int
    {
        return (int) $this->getData(self::POINTS_SPENT);
    }

    /**
     * @inheritDoc
     */
    public function setPointsSpent(int $pointsSpent): PointsBalanceInterface
    {
        return $this->setData(self::POINTS_SPENT, $pointsSpent);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt): PointsBalanceInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
