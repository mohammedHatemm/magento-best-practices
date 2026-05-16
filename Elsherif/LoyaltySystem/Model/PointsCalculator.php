<?php
/**
 * Points Calculator
 * Calculation logic منفصلة عن Business Logic
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Elsherif\LoyaltySystem\Model\Config;
use Magento\Sales\Model\Order;

class PointsCalculator
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Calculate points earned from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return int
     */
    public function calculateEarnedPoints(Order $order): int
    {
        $baseAmount = $order->getBaseGrandTotal();

        // Deduct tax if not earning on tax
        if (!$this->config->isEarnOnTaxEnabled()) {
            $baseAmount -= $order->getBaseTaxAmount();
        }

        // Deduct shipping if not earning on shipping
        if (!$this->config->isEarnOnShippingEnabled()) {
            $baseAmount -= $order->getBaseShippingAmount();
        }

        // Deduct any loyalty discount already applied
        if ($order->getLoyaltyDiscountAmount()) {
            $baseAmount -= abs($order->getLoyaltyDiscountAmount());
        }

        $earnRate = $this->config->getEarnRate();
        $points = (int) floor($baseAmount / $earnRate);

        return max(0, $points);
    }

    /**
     * Calculate discount from points
     *
     * @param int $points
     * @return float
     */
    public function calculateDiscount(int $points): float
    {
        $redeemRate = $this->config->getRedeemRate();
        return round($points / $redeemRate, 2);
    }
}
