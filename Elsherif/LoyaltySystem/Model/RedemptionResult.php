<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Magento\Framework\DataObject;
use Elsherif\LoyaltySystem\Api\Data\RedemptionResultInterface;

class RedemptionResult extends DataObject implements RedemptionResultInterface
{
    public function getSuccess(): bool
    {
        return (bool) $this->getData(self::SUCCESS);
    }

    public function setSuccess(bool $success): RedemptionResultInterface
    {
        return $this->setData(self::SUCCESS, $success);
    }

    public function getMessage(): string
    {
        return (string) $this->getData(self::MESSAGE);
    }

    public function setMessage(string $message): RedemptionResultInterface
    {
        return $this->setData(self::MESSAGE, $message);
    }

    public function getPointsUsed(): int
    {
        return (int) $this->getData(self::POINTS_USED);
    }

    public function setPointsUsed(int $points): RedemptionResultInterface
    {
        return $this->setData(self::POINTS_USED, $points);
    }

    public function getDiscountAmount(): float
    {
        return (float) $this->getData(self::DISCOUNT_AMOUNT);
    }

    public function setDiscountAmount(float $amount): RedemptionResultInterface
    {
        return $this->setData(self::DISCOUNT_AMOUNT, $amount);
    }

    public function getNewBalance(): int
    {
        return (int) $this->getData(self::NEW_BALANCE);
    }

    public function setNewBalance(int $balance): RedemptionResultInterface
    {
        return $this->setData(self::NEW_BALANCE, $balance);
    }
}
