# 🏗️ Phase 1: Module Foundation
## Registration, Configuration & Dependency Injection

---

## 📋 ما سنبنيه في هذه المرحلة:

✅ Module Registration  
✅ Module Declaration  
✅ Composer Configuration  
✅ Dependency Injection Setup  
✅ Admin Configuration (System.xml)  
✅ Default Config Values  
✅ Helper Classes  
✅ ACL Resources  

---

## 📁 File Structure

```
src/app/code/Elsherif/LoyaltySystem/
├── registration.php                 ← ابدأ من هنا
├── composer.json
├── etc/
│   ├── module.xml
│   ├── di.xml
│   ├── config.xml
│   ├── acl.xml
│   └── adminhtml/
│       ├── system.xml
│       └── menu.xml
├── Helper/
│   ├── Data.php
│   └── Email.php
└── Model/
    └── Config.php
```

---

## 📝 Step-by-Step Implementation

### Step 1: Module Registration

**File:** `registration.php`

```php
<?php
/**
 * Copyright © Elsherif. All rights reserved.
 * Module: Loyalty System
 * Author: Elsherif
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Elsherif_LoyaltySystem',
    __DIR__
);
```

**ما يحدث هنا:**
- نسجل الموديول مع Magento
- `ComponentRegistrar::MODULE` - نوع Component (موديول)
- `Elsherif_LoyaltySystem` - اسم الموديول (Vendor_ModuleName)
- `__DIR__` - مسار الموديول الحالي

---

### Step 2: Module Declaration

**File:** `etc/module.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Copyright © Elsherif. All rights reserved.
 * Module: Loyalty System
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="Elsherif_LoyaltySystem" setup_version="1.0.0">
        <sequence>
            <module name="Magento_Customer"/>
            <module name="Magento_Sales"/>
            <module name="Magento_Quote"/>
            <module name="Magento_Checkout"/>
            <module name="Magento_Catalog"/>
        </sequence>
    </module>
</config>
```

**شرح:**
- `setup_version="1.0.0"` - رقم إصدار الموديول
- `<sequence>` - الموديولات اللي لازم تتحمل **قبل** موديولنا
- نحتاج Customer (العملاء)، Sales (الطلبات)، Quote (السلة)، Checkout، Catalog

---

### Step 3: Composer Configuration

**File:** `composer.json`

```json
{
    "name": "elsherif/magento2-loyalty-system",
    "description": "Complete Loyalty Points System with Multi-Frontend Support",
    "type": "magento2-module",
    "version": "1.0.0",
    "license": "MIT",
    "authors": [
        {
            "name": "Elsherif",
            "email": "dev@elsherif.com"
        }
    ],
    "require": {
        "php": "~7.4.0|~8.1.0|~8.2.0",
        "magento/framework": "^103.0",
        "magento/module-customer": "^103.0",
        "magento/module-sales": "^103.0",
        "magento/module-quote": "^103.0",
        "magento/module-checkout": "^103.0"
    },
    "autoload": {
        "files": [
            "registration.php"
        ],
        "psr-4": {
            "Elsherif\\LoyaltySystem\\": ""
        }
    }
}
```

---

### Step 4: Dependency Injection Configuration

**File:** `etc/di.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Dependency Injection Configuration
 * ربط Interfaces بـ Implementations
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    
    <!-- Service Contract Preference -->
    <preference for="Elsherif\LoyaltySystem\Api\PointsManagementInterface"
                type="Elsherif\LoyaltySystem\Model\PointsManagement"/>
    
    <preference for="Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface"
                type="Elsherif\LoyaltySystem\Model\PointsBalance"/>
    
    <preference for="Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface"
                type="Elsherif\LoyaltySystem\Model\PointsTransaction"/>
    
    <!-- Virtual Types for Logging -->
    <virtualType name="LoyaltySystemLogger" type="Magento\Framework\Logger\Monolog">
        <arguments>
            <argument name="name" xsi:type="string">loyalty_system</argument>
            <argument name="handlers" xsi:type="array">
                <item name="system" xsi:type="object">LoyaltySystemLogHandler</item>
            </argument>
        </arguments>
    </virtualType>
    
    <virtualType name="LoyaltySystemLogHandler" type="Magento\Framework\Logger\Handler\Base">
        <arguments>
            <argument name="fileName" xsi:type="string">/var/log/loyalty_system.log</argument>
        </arguments>
    </virtualType>
    
    <!-- Inject Logger into Config -->
    <type name="Elsherif\LoyaltySystem\Model\Config">
        <arguments>
            <argument name="logger" xsi:type="object">LoyaltySystemLogger</argument>
        </arguments>
    </type>
    
</config>
```

**شرح:**
- `<preference>` - ربط Interface بـ Implementation
- `<virtualType>` - إنشاء logger خاص بالموديول
- يكتب logs في `/var/log/loyalty_system.log`

---

### Step 5: Default Configuration Values

**File:** `etc/config.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Default Configuration Values
 * القيم الافتراضية للإعدادات
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Store:etc/config.xsd">
    <default>
        <loyalty_system>
            <!-- General Settings -->
            <general>
                <enabled>1</enabled>
                <earn_rate>10</earn_rate>            <!-- 1 point per 10 currency -->
                <redeem_rate>10</redeem_rate>        <!-- 10 points = 1 currency -->
                <expiration_days>180</expiration_days>
                <min_points_redeem>10</min_points_redeem>
                <max_points_per_order>0</max_points_per_order> <!-- 0 = no limit -->
            </general>
            
            <!-- Earning Rules -->
            <earning_rules>
                <earn_on_tax>0</earn_on_tax>
                <earn_on_shipping>0</earn_on_shipping>
            </earning_rules>
            
            <!-- Email Notifications -->
            <email>
                <send_earn_email>1</send_earn_email>
                <send_expiry_reminder>1</send_expiry_reminder>
                <expiry_reminder_days>7</expiry_reminder_days>
            </email>
        </loyalty_system>
    </default>
</config>
```

---

### Step 6: Admin System Configuration

**File:** `etc/adminhtml/system.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Admin Configuration Form
 * Stores > Configuration > Elsherif Extensions > Loyalty System
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Config:etc/system_file.xsd">
    <system>
        <!-- Tab: Elsherif Extensions -->
        <tab id="elsherif" translate="label" sortOrder="400">
            <label>Elsherif Extensions</label>
        </tab>
        
        <!-- Section: Loyalty System -->
        <section id="loyalty_system" translate="label" type="text" sortOrder="100" 
                 showInDefault="1" showInWebsite="1" showInStore="1">
            <label>Loyalty System</label>
            <tab>elsherif</tab>
            <resource>Elsherif_LoyaltySystem::config</resource>
            
            <!-- Group 1: General Settings -->
            <group id="general" translate="label" type="text" sortOrder="10" 
                   showInDefault="1" showInWebsite="1" showInStore="1">
                <label>General Settings</label>
                
                <field id="enabled" translate="label" type="select" sortOrder="10" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Enable Loyalty System</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                
                <field id="earn_rate" translate="label comment" type="text" sortOrder="20" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Points Earning Rate</label>
                    <comment><![CDATA[Customer earns <strong>1 point</strong> for every <strong>X</strong> amount spent]]></comment>
                    <validate>required-entry validate-number validate-greater-than-zero</validate>
                    <depends>
                        <field id="enabled">1</field>
                    </depends>
                </field>
                
                <field id="redeem_rate" translate="label comment" type="text" sortOrder="30" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Points Redemption Rate</label>
                    <comment><![CDATA[<strong>X points</strong> = <strong>1 currency unit</strong> discount]]></comment>
                    <validate>required-entry validate-number validate-greater-than-zero</validate>
                    <depends>
                        <field id="enabled">1</field>
                    </depends>
                </field>
                
                <field id="expiration_days" translate="label comment" type="text" sortOrder="40" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Points Expiration (Days)</label>
                    <comment>Points will expire after X days. Set to 0 for no expiration.</comment>
                    <validate>validate-number validate-zero-or-greater</validate>
                    <depends>
                        <field id="enabled">1</field>
                    </depends>
                </field>
                
                <field id="min_points_redeem" translate="label comment" type="text" sortOrder="50" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Minimum Points to Redeem</label>
                    <comment>Minimum points required to use redemption feature</comment>
                    <validate>validate-number validate-zero-or-greater</validate>
                    <depends>
                        <field id="enabled">1</field>
                    </depends>
                </field>
                
                <field id="max_points_per_order" translate="label comment" type="text" sortOrder="60" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Maximum Points Per Order</label>
                    <comment>Maximum points that can be redeemed in a single order. Leave 0 for no limit.</comment>
                    <validate>validate-number validate-zero-or-greater</validate>
                    <depends>
                        <field id="enabled">1</field>
                    </depends>
                </field>
            </group>
            
            <!-- Group 2: Earning Rules -->
            <group id="earning_rules" translate="label" type="text" sortOrder="20" 
                   showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Earning Rules</label>
                
                <field id="earn_on_tax" translate="label" type="select" sortOrder="10" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Earn Points on Tax Amount</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                
                <field id="earn_on_shipping" translate="label" type="select" sortOrder="20" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Earn Points on Shipping Amount</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
            </group>
            
            <!-- Group 3: Email Notifications -->
            <group id="email" translate="label" type="text" sortOrder="30" 
                   showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Email Notifications</label>
                
                <field id="send_earn_email" translate="label" type="select" sortOrder="10" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Send Email When Points Earned</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                
                <field id="send_expiry_reminder" translate="label" type="select" sortOrder="20" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Send Expiry Reminder Email</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                
                <field id="expiry_reminder_days" translate="label comment" type="text" sortOrder="30" 
                       showInDefault="1" showInWebsite="1" showInStore="1" canRestore="1">
                    <label>Reminder Days Before Expiry</label>
                    <comment>Send reminder email X days before points expire</comment>
                    <validate>validate-number validate-greater-than-zero</validate>
                    <depends>
                        <field id="send_expiry_reminder">1</field>
                    </depends>
                </field>
            </group>
        </section>
    </system>
</config>
```

---

### Step 7: Admin Menu Configuration

**File:** `etc/adminhtml/menu.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Admin Menu
 * Marketing > Loyalty System
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Backend:etc/menu.xsd">
    <menu>
        <!-- Main Menu Item -->
        <add id="Elsherif_LoyaltySystem::loyalty"
             title="Loyalty System"
             translate="title"
             module="Elsherif_LoyaltySystem"
             sortOrder="100"
             parent="Magento_Backend::marketing"
             resource="Elsherif_LoyaltySystem::loyalty"/>
        
        <!-- Sub Menu: Points Balances -->
        <add id="Elsherif_LoyaltySystem::points_balances"
             title="Points Balances"
             translate="title"
             module="Elsherif_LoyaltySystem"
             sortOrder="10"
             parent="Elsherif_LoyaltySystem::loyalty"
             action="loyalty/points/index"
             resource="Elsherif_LoyaltySystem::points_view"/>
        
        <!-- Sub Menu: Transactions -->
        <add id="Elsherif_LoyaltySystem::transactions"
             title="Transactions"
             translate="title"
             module="Elsherif_LoyaltySystem"
             sortOrder="20"
             parent="Elsherif_LoyaltySystem::loyalty"
             action="loyalty/transaction/index"
             resource="Elsherif_LoyaltySystem::transactions_view"/>
        
        <!-- Sub Menu: Configuration -->
        <add id="Elsherif_LoyaltySystem::config"
             title="Configuration"
             translate="title"
             module="Elsherif_LoyaltySystem"
             sortOrder="100"
             parent="Elsherif_LoyaltySystem::loyalty"
             action="adminhtml/system_config/edit/section/loyalty_system"
             resource="Elsherif_LoyaltySystem::config"/>
    </menu>
</config>
```

---

### Step 8: ACL Resources (Permissions)

**File:** `etc/acl.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Access Control List (ACL)
 * تحديد صلاحيات الأدمن
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Acl/etc/acl.xsd">
    <acl>
        <resources>
            <resource id="Magento_Backend::admin">
                <resource id="Elsherif_LoyaltySystem::loyalty" title="Loyalty System" translate="title" sortOrder="100">
                    
                    <!-- View Points -->
                    <resource id="Elsherif_LoyaltySystem::points_view" 
                              title="View Points Balances" 
                              translate="title" 
                              sortOrder="10"/>
                    
                    <!-- Manage Points -->
                    <resource id="Elsherif_LoyaltySystem::points_manage" 
                              title="Manage Points (Add/Deduct)" 
                              translate="title" 
                              sortOrder="20"/>
                    
                    <!-- View Transactions -->
                    <resource id="Elsherif_LoyaltySystem::transactions_view" 
                              title="View Transactions" 
                              translate="title" 
                              sortOrder="30"/>
                    
                    <!-- Configuration -->
                    <resource id="Elsherif_LoyaltySystem::config" 
                              title="Configuration" 
                              translate="title" 
                              sortOrder="100"/>
                </resource>
                
                <!-- Add to System Config -->
                <resource id="Magento_Backend::stores">
                    <resource id="Magento_Backend::stores_settings">
                        <resource id="Magento_Config::config">
                            <resource id="Elsherif_LoyaltySystem::config" 
                                      title="Loyalty System Configuration" 
                                      translate="title"/>
                        </resource>
                    </resource>
                </resource>
            </resource>
        </resources>
    </acl>
</config>
```

---

### Step 9: Configuration Helper Class

**File:** `Model/Config.php`

```php
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
```

---

### Step 10: Data Helper

**File:** `Helper/Data.php`

```php
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
```

---

### Step 11: Email Helper

**File:** `Helper/Email.php`

```php
<?php
/**
 * Email Helper
 * سنستخدمها في Phase 4
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Psr\Log\LoggerInterface;

class Email extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Send points earned email
     *
     * @param string $customerEmail
     * @param string $customerName
     * @param int $points
     * @param int $newBalance
     * @return bool
     */
    public function sendPointsEarnedEmail(
        string $customerEmail,
        string $customerName,
        int $points,
        int $newBalance
    ): bool {
        if (!$this->config->isSendEarnEmailEnabled()) {
            return false;
        }

        try {
            $this->inlineTranslation->suspend();

            $templateVars = [
                'customer_name' => $customerName,
                'points_earned' => $points,
                'new_balance' => $newBalance
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('loyalty_points_earned')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($customerEmail, $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error sending points earned email: ' . $e->getMessage());
            return false;
        }
    }
}
```

---

## ✅ Testing Phase 1

### Enable the Module

```bash
# في الـ Terminal
cd /path/to/magento

# Enable module
php bin/magento module:enable Elsherif_LoyaltySystem

# Run setup upgrade
php bin/magento setup:upgrade

# Check module status
php bin/magento module:status Elsherif_LoyaltySystem
```

**Expected Output:**
```
Module is enabled
```

### Verify Admin Configuration

1. Login to Admin Panel
2. Go to **Stores → Configuration**
3. في الـ Sidebar اليسار، تحت **Elsherif Extensions**، لازم تلاقي **Loyalty System**
4. افتح الصفحة وشوف الإعدادات

### Verify ACL

1. Go to **System → User Roles**
2. Edit any role
3. في **Role Resources**، لازم تلاقي:
   - Elsherif Extensions
     - Loyalty System
       - View Points Balances
       - Manage Points
       - View Transactions
       - Configuration

---

## 🎯 ما تم إنجازه في Phase 1:

✅ Module Registration  
✅ Module Declaration with Dependencies  
✅ Composer Configuration  
✅ Dependency Injection Setup  
✅ Default Configuration Values  
✅ Admin System Configuration Form  
✅ Admin Menu Structure  
✅ ACL Permissions  
✅ Config Helper Class  
✅ Data Helper Class  
✅ Email Helper Class (للاستخدام لاحقاً)  
✅ Custom Logger  

---

## 🚀 Next Phase

**Phase 2: Database & Models Layer**
- Database Schema (db_schema.xml)
- Models & ResourceModels
- Collections
- Repositories

---

**Ready to proceed to Phase 2? 🎉**
