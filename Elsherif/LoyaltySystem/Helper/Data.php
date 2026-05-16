<?php
/**
 * General Data Helper
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Elsherif\LoyaltySystem\Model\Config;

class Data extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param Context $context
     * @param Config $config
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        Config $config,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Format price
     *
     * @param float $amount
     * @return string
     */
    public function formatPrice(float $amount): string
    {
        return $this->priceCurrency->format($amount, false);
    }

    /**
     * Calculate points value in currency
     *
     * @param int $points
     * @return float
     */
    public function calculatePointsValue(int $points): float
    {
        $redeemRate = $this->config->getRedeemRate();
        return $points / $redeemRate;
    }

    /**
     * Calculate discount from points
     *
     * @param int $points
     * @return float
     */
    public function calculateDiscount(int $points): float
    {
        return $this->calculatePointsValue($points);
    }

    /**
     * Get expiration date
     *
     * @return string|null
     */
    public function getExpirationDate(): ?string
    {
        $days = $this->config->getExpirationDays();
        
        if ($days <= 0) {
            return null; // No expiration
        }

        $date = new \DateTime();
        $date->modify("+{$days} days");
        
        return $date->format('Y-m-d H:i:s');
    }
}
