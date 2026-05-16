<?php
/**
 * Configuration Helper
 * قراءة الإعدادات من Admin
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Config
{
    /** Config Paths */
    const XML_PATH_ENABLED = 'loyalty_system/general/enabled';
    const XML_PATH_EARN_RATE = 'loyalty_system/general/earn_rate';
    const XML_PATH_REDEEM_RATE = 'loyalty_system/general/redeem_rate';
    const XML_PATH_EXPIRATION_DAYS = 'loyalty_system/general/expiration_days';
    const XML_PATH_MIN_POINTS_REDEEM = 'loyalty_system/general/min_points_redeem';
    const XML_PATH_MAX_POINTS_PER_ORDER = 'loyalty_system/general/max_points_per_order';
    const XML_PATH_EARN_ON_TAX = 'loyalty_system/earning_rules/earn_on_tax';
    const XML_PATH_EARN_ON_SHIPPING = 'loyalty_system/earning_rules/earn_on_shipping';
    const XML_PATH_SEND_EARN_EMAIL = 'loyalty_system/email/send_earn_email';
    const XML_PATH_SEND_EXPIRY_REMINDER = 'loyalty_system/email/send_expiry_reminder';
    const XML_PATH_EXPIRY_REMINDER_DAYS = 'loyalty_system/email/expiry_reminder_days';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Check if loyalty system is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_ENABLED, $storeId);
    }

    /**
     * Get earning rate (1 point per X currency)
     *
     * @param int|null $storeId
     * @return float
     */
    public function getEarnRate(?int $storeId = null): float
    {
        $rate = (float) $this->getConfigValue(self::XML_PATH_EARN_RATE, $storeId);
        return $rate > 0 ? $rate : 10.0;
    }

    /**
     * Get redemption rate (X points = 1 currency)
     *
     * @param int|null $storeId
     * @return float
     */
    public function getRedeemRate(?int $storeId = null): float
    {
        $rate = (float) $this->getConfigValue(self::XML_PATH_REDEEM_RATE, $storeId);
        return $rate > 0 ? $rate : 10.0;
    }

    /**
     * Get expiration days
     *
     * @param int|null $storeId
     * @return int
     */
    public function getExpirationDays(?int $storeId = null): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_EXPIRATION_DAYS, $storeId);
    }

    /**
     * Get minimum points to redeem
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMinPointsToRedeem(?int $storeId = null): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_MIN_POINTS_REDEEM, $storeId);
    }

    /**
     * Get maximum points per order (0 = no limit)
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMaxPointsPerOrder(?int $storeId = null): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_MAX_POINTS_PER_ORDER, $storeId);
    }

    /**
     * Check if earn on tax is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEarnOnTaxEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_EARN_ON_TAX, $storeId);
    }

    /**
     * Check if earn on shipping is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEarnOnShippingEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_EARN_ON_SHIPPING, $storeId);
    }

    /**
     * Check if send earn email is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isSendEarnEmailEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_SEND_EARN_EMAIL, $storeId);
    }

    /**
     * Check if send expiry reminder is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isSendExpiryReminderEnabled(?int $storeId = null): bool
    {
        return (bool) $this->getConfigValue(self::XML_PATH_SEND_EXPIRY_REMINDER, $storeId);
    }

    /**
     * Get expiry reminder days
     *
     * @param int|null $storeId
     * @return int
     */
    public function getExpiryReminderDays(?int $storeId = null): int
    {
        return (int) $this->getConfigValue(self::XML_PATH_EXPIRY_REMINDER_DAYS, $storeId);
    }

    /**
     * Get config value
     *
     * @param string $path
     * @param int|null $storeId
     * @return mixed
     */
    private function getConfigValue(string $path, ?int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
