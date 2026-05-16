<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api\Data;

interface RedemptionResultInterface
{
    const SUCCESS = 'success';
    const MESSAGE = 'message';
    const POINTS_USED = 'points_used';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const NEW_BALANCE = 'new_balance';

    /**
     * Get success status
     *
     * @return bool
     */
    public function getSuccess(): bool;

    /**
     * Set success status
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success): self;

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self;

    /**
     * Get points used
     *
     * @return int
     */
    public function getPointsUsed(): int;

    /**
     * Set points used
     *
     * @param int $points
     * @return $this
     */
    public function setPointsUsed(int $points): self;

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountAmount(): float;

    /**
     * Set discount amount
     *
     * @param float $amount
     * @return $this
     */
    public function setDiscountAmount(float $amount): self;

    /**
     * Get new balance
     *
     * @return int
     */
    public function getNewBalance(): int;

    /**
     * Set new balance
     *
     * @param int $balance
     * @return $this
     */
    public function setNewBalance(int $balance): self;
}
