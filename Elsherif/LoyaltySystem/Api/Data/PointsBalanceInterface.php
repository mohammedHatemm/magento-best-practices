<?php
/**
 * Points Balance Data Interface
 * Service Contract for Points Balance
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api\Data;

interface PointsBalanceInterface
{
    /**
     * Constants for keys
     */
    const BALANCE_ID = 'balance_id';
    const CUSTOMER_ID = 'customer_id';
    const POINTS = 'points';
    const LIFETIME_POINTS = 'lifetime_points';
    const POINTS_SPENT = 'points_spent';
    const UPDATED_AT = 'updated_at';

    /**
     * Get balance ID
     *
     * @return int|null
     */
    public function getBalanceId(): ?int;

    /**
     * Set balance ID
     *
     * @param int $balanceId
     * @return $this
     */
    public function setBalanceId(int $balanceId): self;

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
     * Get current points
     *
     * @return int
     */
    public function getPoints(): int;

    /**
     * Set current points
     *
     * @param int $points
     * @return $this
     */
    public function setPoints(int $points): self;

    /**
     * Get lifetime points
     *
     * @return int
     */
    public function getLifetimePoints(): int;

    /**
     * Set lifetime points
     *
     * @param int $lifetimePoints
     * @return $this
     */
    public function setLifetimePoints(int $lifetimePoints): self;

    /**
     * Get points spent
     *
     * @return int
     */
    public function getPointsSpent(): int;

    /**
     * Set points spent
     *
     * @param int $pointsSpent
     * @return $this
     */
    public function setPointsSpent(int $pointsSpent): self;

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): self;
}
