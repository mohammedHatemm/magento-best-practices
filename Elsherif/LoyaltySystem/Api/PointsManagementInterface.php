<?php
/**
 * Points Management Service Contract
 * Main interface للتعامل مع النقاط
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api;

use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Elsherif\LoyaltySystem\Api\Data\RedemptionResultInterface;

interface PointsManagementInterface
{
    /**
     * Add points to customer
     *
     * @param int $customerId
     * @param int $points
     * @param string $action
     * @param int|null $referenceId
     * @param string|null $expiresAt
     * @param string|null $comment
     * @return bool
     */
    public function addPoints(
        int $customerId,
        int $points,
        string $action,
        ?int $referenceId = null,
        ?string $expiresAt = null,
        ?string $comment = null
    ): bool;

    /**
     * Deduct points from customer
     *
     * @param int $customerId
     * @param int $points
     * @param string $action
     * @param int|null $referenceId
     * @param string|null $comment
     * @return bool
     */
    public function deductPoints(
        int $customerId,
        int $points,
        string $action,
        ?int $referenceId = null,
        ?string $comment = null
    ): bool;

    /**
     * Get customer points balance
     *
     * @param int $customerId
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface
     */
    public function getBalance(int $customerId): PointsBalanceInterface;

    /**
     * Redeem points for discount
     *
     * @param int $quoteId
     * @param int $points
     * @return \Elsherif\LoyaltySystem\Api\Data\RedemptionResultInterface
     */
    public function redeemPoints(int $quoteId, int $points): RedemptionResultInterface;

    /**
     * Cancel points redemption
     *
     * @param int $quoteId
     * @return bool
     */
    public function cancelRedemption(int $quoteId): bool;
}