<?php
/**
 * Points Transaction Model
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Magento\Framework\Model\AbstractModel;
use Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction as PointsTransactionResource;

class PointsTransaction extends AbstractModel implements PointsTransactionInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'elsherif_loyalty_points_transaction';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PointsTransactionResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId(): ?int
    {
        return $this->getData(self::TRANSACTION_ID) 
            ? (int) $this->getData(self::TRANSACTION_ID) 
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId(int $transactionId): PointsTransactionInterface
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
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
    public function setCustomerId(int $customerId): PointsTransactionInterface
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
    public function setPoints(int $points): PointsTransactionInterface
    {
        return $this->setData(self::POINTS, $points);
    }

    /**
     * @inheritDoc
     */
    public function getBalanceAfter(): int
    {
        return (int) $this->getData(self::BALANCE_AFTER);
    }

    /**
     * @inheritDoc
     */
    public function setBalanceAfter(int $balanceAfter): PointsTransactionInterface
    {
        return $this->setData(self::BALANCE_AFTER, $balanceAfter);
    }

    /**
     * @inheritDoc
     */
    public function getAction(): string
    {
        return (string) $this->getData(self::ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setAction(string $action): PointsTransactionInterface
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * @inheritDoc
     */
    public function getReferenceId(): ?int
    {
        return $this->getData(self::REFERENCE_ID) 
            ? (int) $this->getData(self::REFERENCE_ID) 
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setReferenceId(?int $referenceId): PointsTransactionInterface
    {
        return $this->setData(self::REFERENCE_ID, $referenceId);
    }

    /**
     * @inheritDoc
     */
    public function getReferenceType(): ?string
    {
        return $this->getData(self::REFERENCE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setReferenceType(?string $referenceType): PointsTransactionInterface
    {
        return $this->setData(self::REFERENCE_TYPE, $referenceType);
    }

    /**
     * @inheritDoc
     */
    public function getExpiresAt(): ?string
    {
        return $this->getData(self::EXPIRES_AT);
    }

    /**
     * @inheritDoc
     */
    public function setExpiresAt(?string $expiresAt): PointsTransactionInterface
    {
        return $this->setData(self::EXPIRES_AT, $expiresAt);
    }

    /**
     * @inheritDoc
     */
    public function getComment(): ?string
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * @inheritDoc
     */
    public function setComment(?string $comment): PointsTransactionInterface
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): PointsTransactionInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
