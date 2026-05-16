<?php
/**
 * Points Transaction Data Interface
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api\Data;

interface PointsTransactionInterface
{
    /**
     * Constants for keys
     */
    const TRANSACTION_ID = 'transaction_id';
    const CUSTOMER_ID = 'customer_id';
    const POINTS = 'points';
    const BALANCE_AFTER = 'balance_after';
    const ACTION = 'action';
    const REFERENCE_ID = 'reference_id';
    const REFERENCE_TYPE = 'reference_type';
    const EXPIRES_AT = 'expires_at';
    const COMMENT = 'comment';
    const CREATED_AT = 'created_at';

    /**
     * Action Types
     */
    const ACTION_ORDER_COMPLETE = 'order_complete';
    const ACTION_REDEMPTION = 'redemption';
    const ACTION_EXPIRED = 'expired';
    const ACTION_ADMIN_ADJUST = 'admin_adjust';
    const ACTION_REFUND = 'refund';

    /**
     * Get transaction ID
     *
     * @return int|null
     */
    public function getTransactionId(): ?int;

    /**
     * Set transaction ID
     *
     * @param int $transactionId
     * @return $this
     */
    public function setTransactionId(int $transactionId): self;

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * Get points
     *
     * @return int
     */
    public function getPoints(): int;

    /**
     * Set points
     *
     * @param int $points
     * @return $this
     */
    public function setPoints(int $points): self;

    /**
     * Get balance after transaction
     *
     * @return int
     */
    public function getBalanceAfter(): int;

    /**
     * Set balance after transaction
     *
     * @param int $balanceAfter
     * @return $this
     */
    public function setBalanceAfter(int $balanceAfter): self;

    /**
     * Get action
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Set action
     *
     * @param string $action
     * @return $this
     */
    public function setAction(string $action): self;

    /**
     * Get reference ID
     *
     * @return int|null
     */
    public function getReferenceId(): ?int;

    /**
     * Set reference ID
     *
     * @param int|null $referenceId
     * @return $this
     */
    public function setReferenceId(?int $referenceId): self;

    /**
     * Get reference type
     *
     * @return string|null
     */
    public function getReferenceType(): ?string;

    /**
     * Set reference type
     *
     * @param string|null $referenceType
     * @return $this
     */
    public function setReferenceType(?string $referenceType): self;

    /**
     * Get expires at
     *
     * @return string|null
     */
    public function getExpiresAt(): ?string;

    /**
     * Set expires at
     *
     * @param string|null $expiresAt
     * @return $this
     */
    public function setExpiresAt(?string $expiresAt): self;

    /**
     * Get comment
     *
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * Set comment
     *
     * @param string|null $comment
     * @return $this
     */
    public function setComment(?string $comment): self;

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;
}
