<?php
/**
 * Loyalty System Configuration Helper
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    // General Settings
    private const XML_PATH_ENABLED = 'loyalty_system/general/enabled';
    private const XML_PATH_EARN_RATE = 'loyalty_system/general/earn_rate';
    private const XML_PATH_REDEEM_RATE = 'loyalty_system/general/redeem_rate';
    private const XML_PATH_MIN_POINTS_REDEEM = 'loyalty_system/general/min_points_redeem';
    private const XML_PATH_MAX_POINTS_ORDER = 'loyalty_system/general/max_points_per_order';
    private const XML_PATH_POINTS_EXPIRY = 'loyalty_system/general/expiration_days';
    
    // Earning Rules
    private const XML_PATH_EARN_ON_TAX = 'loyalty_system/earning_rules/earn_on_tax';
    private const XML_PATH_EARN_ON_SHIPPING = 'loyalty_system/earning_rules/earn_on_shipping';
    
    // Email Settings
    private const XML_PATH_SEND_EARN_EMAIL = 'loyalty_system/email/send_earn_email';
    private const XML_PATH_SEND_EXPIRY_REMINDER = 'loyalty_system/email/send_expiry_reminder';
    private const XML_PATH_EXPIRY_REMINDER_DAYS = 'loyalty_system/email/expiry_reminder_days';

    /**
     * Check if loyalty system is enabled
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get earning rate (points per currency unit)
     */
    public function getEarnRate(?int $storeId = null): float
    {
        $rate = $this->scopeConfig->getValue(
            self::XML_PATH_EARN_RATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        return (float) ($rate ?: 1.0);
    }

    /**
     * Get redemption rate (points per currency unit)
     */
    public function getRedeemRate(?int $storeId = null): float
    {
        $rate = $this->scopeConfig->getValue(
            self::XML_PATH_REDEEM_RATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        return (float) ($rate ?: 10.0);
    }

    /**
     * Get minimum points required to redeem
     */
    public function getMinPointsToRedeem(?int $storeId = null): int
    {
        $min = $this->scopeConfig->getValue(
            self::XML_PATH_MIN_POINTS_REDEEM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        return (int) ($min ?: 10);
    }

    /**
     * Get maximum points per order
     */
    public function getMaxPointsPerOrder(?int $storeId = null): ?int
    {
        $max = $this->scopeConfig->getValue(
            self::XML_PATH_MAX_POINTS_ORDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        return $max ? (int) $max : null;
    }

    /**
     * Get points expiry days
     */
    public function getPointsExpiryDays(?int $storeId = null): int
    {
        $days = $this->scopeConfig->getValue(
            self::XML_PATH_POINTS_EXPIRY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        return (int) ($days ?: 365);
    }

    /**
     * Check if should earn points on tax
     */
    public function earnOnTax(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EARN_ON_TAX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if should earn points on shipping
     */
    public function earnOnShipping(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EARN_ON_SHIPPING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if should send email when points are earned
     */
    public function shouldSendEarnEmail(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_EARN_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if should send expiry reminder email
     */
    public function shouldSendExpiryReminder(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_EXPIRY_REMINDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get expiry reminder days
     */
    public function getExpiryReminderDays(?int $storeId = null): int
    {
        $days = $this->scopeConfig->getValue(
            self::XML_PATH_EXPIRY_REMINDER_DAYS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        
        return (int) ($days ?: 7);
    }

    /**
     * Calculate discount from points
     */
    public function calculateDiscount(int $points, ?int $storeId = null): float
    {
        $rate = $this->getRedeemRate($storeId);
        return $rate > 0 ? $points / $rate : 0;
    }

    /**
     * Calculate points from amount
     */
    public function calculatePoints(float $amount, ?int $storeId = null): int
    {
        $rate = $this->getEarnRate($storeId);
        return $rate > 0 ? (int) floor($amount / $rate) : 0;
    }
}
