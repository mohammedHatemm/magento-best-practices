# ⚙️ Phase 4: Business Logic
## Observers, Plugins, Cron Jobs & Email

---

## 📋 ما سنبنيه في هذه المرحلة:

✅ Event Observers (كسب/خصم النقاط)  
✅ Plugins (تطبيق الخصم على Quote)  
✅ Cron Jobs (انتهاء الصلاحية)  
✅ Email Templates  
✅ CLI Commands  
✅ Customer Data Sections  

---

## 📁 File Structure

```
src/app/code/Elsherif/LoyaltySystem/
├── etc/
│   ├── events.xml                 ← Event observers
│   ├── crontab.xml                ← Cron schedule
│   └── frontend/
│       └── sections.xml           ← Customer data invalidation
│
├── Observer/
│   ├── EarnPointsOnOrderObserver.php
│   ├── DeductPointsOnOrderObserver.php
│   └── RefundPointsObserver.php
│
├── Plugin/
│   ├── Quote/
│   │   └── Model/
│   │       └── QuoteTotalPlugin.php
│   └── Sales/
│       └── Model/
│           └── OrderPlugin.php
│
├── Cron/
│   └── ExpirePoints.php
│
├── Console/
│   └── Command/
│       ├── AddPointsCommand.php
│       └── ExpirePointsCommand.php
│
└── view/
    └── frontend/
        └── email/
            ├── points_earned.html
            └── points_expiring.html
```

---

## 📝 Step-by-Step Implementation

### Step 1: Event Configuration

**File:** `etc/events.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    
    <!-- Earn points when order is complete -->
    <event name="sales_order_save_after">
        <observer name="loyalty_earn_points" 
                  instance="Elsherif\LoyaltySystem\Observer\EarnPointsOnOrderObserver"/>
    </event>

    <!-- Deduct points when order is placed -->
    <event name="sales_order_place_after">
        <observer name="loyalty_deduct_points" 
                  instance="Elsherif\LoyaltySystem\Observer\DeductPointsOnOrderObserver"/>
    </event>

    <!-- Refund points when order is refunded -->
    <event name="sales_order_creditmemo_save_after">
        <observer name="loyalty_refund_points" 
                  instance="Elsherif\LoyaltySystem\Observer\RefundPointsObserver"/>
    </event>
</config>
```

---

### Step 2: Earn Points Observer

**File:** `Observer/EarnPointsOnOrderObserver.php`

```php
<?php
/**
 * Earn Points When Order is Complete
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Elsherif\LoyaltySystem\Model\PointsCalculator;
use Elsherif\LoyaltySystem\Helper\Data as DataHelper;
use Elsherif\LoyaltySystem\Helper\Email as EmailHelper;
use Psr\Log\LoggerInterface;

class EarnPointsOnOrderObserver implements ObserverInterface
{
    private $pointsManagement;
    private $config;
    private $calculator;
    private $dataHelper;
    private $emailHelper;
    private $logger;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        Config $config,
        PointsCalculator $calculator,
        DataHelper $dataHelper,
        EmailHelper $emailHelper,
        LoggerInterface $logger
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->calculator = $calculator;
        $this->dataHelper = $dataHelper;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            // Validate
            if (!$this->isEligible($order)) {
                return;
            }

            // Calculate points
            $points = $this->calculator->calculateEarnedPoints($order);

            if ($points <= 0) {
                return;
            }

            // Add points
            $expiresAt = $this->dataHelper->getExpirationDate();
            
            $this->pointsManagement->addPoints(
                (int) $order->getCustomerId(),
                $points,
                'order_complete',
                (int) $order->getId(),
                $expiresAt,
                "Earned from order #{$order->getIncrementId()}"
            );

            // Save to order extension attributes
            $extensionAttributes = $order->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributes->setLoyaltyPointsEarned($points);
                $order->setExtensionAttributes($extensionAttributes);
            }

            // Send email
            $balance = $this->pointsManagement->getBalance((int) $order->getCustomerId());
            
            $this->emailHelper->sendPointsEarnedEmail(
                $order->getCustomerEmail(),
                $order->getCustomerName(),
                $points,
                $balance->getPoints()
            );

            $this->logger->info("Loyalty: Added {$points} points to customer {$order->getCustomerId()}");

        } catch (\Exception $e) {
            $this->logger->error('Error earning points: ' . $e->getMessage());
        }
    }

    private function isEligible(Order $order): bool
    {
        // Check if module enabled
        if (!$this->config->isEnabled()) {
            return false;
        }

        // Must have customer
        if (!$order->getCustomerId()) {
            return false;
        }

        // Check order state
        if (!in_array($order->getState(), [Order::STATE_COMPLETE, Order::STATE_PROCESSING])) {
            return false;
        }

        // Don't earn twice
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getLoyaltyPointsEarned()) {
            return false;
        }

        return true;
    }
}
```

---

### Step 3: Deduct Points Observer

**File:** `Observer/DeductPointsOnOrderObserver.php`

```php
<?php
/**
 * Deduct Points When Order is Placed
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Psr\Log\LoggerInterface;

class DeductPointsOnOrderObserver implements ObserverInterface
{
    private $pointsManagement;
    private $logger;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        LoggerInterface $logger
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();

            if (!$order->getCustomerId()) {
                return;
            }

            // Get points used from quote
            $quote = $order->getQuote();
            if (!$quote) {
                return;
            }

            $extensionAttributes = $quote->getExtensionAttributes();
            if (!$extensionAttributes) {
                return;
            }

            $pointsUsed = $extensionAttributes->getLoyaltyPointsUsed();
            if (!$pointsUsed || $pointsUsed <= 0) {
                return;
            }

            // Deduct points
            $this->pointsManagement->deductPoints(
                (int) $order->getCustomerId(),
                $pointsUsed,
                'redemption',
                (int) $order->getId(),
                "Redeemed on order #{$order->getIncrementId()}"
            );

            // Save to order
            $orderExtension = $order->getExtensionAttributes();
            if ($orderExtension) {
                $orderExtension->setLoyaltyPointsUsed($pointsUsed);
                $orderExtension->setLoyaltyDiscountAmount(
                    $extensionAttributes->getLoyaltyDiscountAmount()
                );
                $order->setExtensionAttributes($orderExtension);
            }

            $this->logger->info("Loyalty: Deducted {$pointsUsed} points from customer {$order->getCustomerId()}");

        } catch (\Exception $e) {
            $this->logger->error('Error deducting points: ' . $e->getMessage());
        }
    }
}
```

---

### Step 4: Quote Total Plugin (Apply Discount)

**File:** `Plugin/Quote/Model/QuoteTotalPlugin.php`

```php
<?php
/**
 * Plugin to Apply Loyalty Discount to Quote Totals
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Quote\Model;

use Magento\Quote\Model\Quote;
use Elsherif\LoyaltySystem\Model\PointsCalculator;

class QuoteTotalPlugin
{
    private $calculator;

    public function __construct(PointsCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Apply loyalty discount after totals collection
     *
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     */
    public function afterCollectTotals(Quote $subject, $result)
    {
        $extensionAttributes = $subject->getExtensionAttributes();
        
        if (!$extensionAttributes) {
            return $result;
        }

        $pointsUsed = $extensionAttributes->getLoyaltyPointsUsed();
        
        if (!$pointsUsed || $pointsUsed <= 0) {
            return $result;
        }

        // Calculate discount
        $discount = $this->calculator->calculateDiscount($pointsUsed);

        // Apply discount
        $subject->setSubtotal($subject->getSubtotal() - $discount);
        $subject->setBaseSubtotal($subject->getBaseSubtotal() - $discount);
        $subject->setGrandTotal($subject->getGrandTotal() - $discount);
        $subject->setBaseGrandTotal($subject->getBaseGrandTotal() - $discount);

        // Store discount amount
        $extensionAttributes->setLoyaltyDiscountAmount($discount);
        $subject->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
```

**Register Plugin in `etc/di.xml`:**

```xml
<type name="Magento\Quote\Model\Quote">
    <plugin name="loyalty_apply_discount" 
            type="Elsherif\LoyaltySystem\Plugin\Quote\Model\QuoteTotalPlugin" 
            sortOrder="100"/>
</type>
```

---

### Step 5: Cron Configuration

**File:** `etc/crontab.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Cron:etc/crontab.xsd">
    <group id="default">
        <!-- Run daily at 2:00 AM -->
        <job name="loyalty_expire_points" 
             instance="Elsherif\LoyaltySystem\Cron\ExpirePoints" 
             method="execute">
            <schedule>0 2 * * *</schedule>
        </job>
    </group>
</config>
```

---

### Step 6: Cron Job

**File:** `Cron/ExpirePoints.php`

```php
<?php
/**
 * Expire Points Cron Job
 * Runs daily to expire old points
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Cron;

use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction\CollectionFactory;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Psr\Log\LoggerInterface;

class ExpirePoints
{
    private $transactionCollectionFactory;
    private $pointsManagement;
    private $config;
    private $logger;

    public function __construct(
        CollectionFactory $transactionCollectionFactory,
        PointsManagementInterface $pointsManagement,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $this->logger->info('Loyalty: Starting points expiration job');

        try {
            // Get expired transactions
            $collection = $this->transactionCollectionFactory->create();
            $collection->filterExpired();

            $expiredCount = 0;
            $totalPoints = 0;

            foreach ($collection as $transaction) {
                $points = abs($transaction->getPoints());

                // Deduct expired points
                $this->pointsManagement->deductPoints(
                    $transaction->getCustomerId(),
                    $points,
                    'expired',
                    $transaction->getTransactionId(),
                    "Points expired from transaction #{$transaction->getTransactionId()}"
                );

                $expiredCount++;
                $totalPoints += $points;
            }

            $this->logger->info("Loyalty: Expired {$totalPoints} points from {$expiredCount} transactions");

        } catch (\Exception $e) {
            $this->logger->error('Loyalty expiration error: ' . $e->getMessage());
        }
    }
}
```

---

### Step 7: CLI Commands

**File:** `Console/Command/AddPointsCommand.php`

```php
<?php
/**
 * CLI Command to Add Points
 * php bin/magento loyalty:points:add 1 100
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;

class AddPointsCommand extends Command
{
    private $pointsManagement;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        string $name = null
    ) {
        $this->pointsManagement = $pointsManagement;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('loyalty:points:add')
            ->setDescription('Add points to customer')
            ->addArgument('customer_id', InputArgument::REQUIRED, 'Customer ID')
            ->addArgument('points', InputArgument::REQUIRED, 'Points to add');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $customerId = (int) $input->getArgument('customer_id');
        $points = (int) $input->getArgument('points');

        try {
            $this->pointsManagement->addPoints(
                $customerId,
                $points,
                'admin_adjust',
                null,
                null,
                'Added via CLI'
            );

            $balance = $this->pointsManagement->getBalance($customerId);

            $output->writeln("<info>Success! Added {$points} points to customer {$customerId}</info>");
            $output->writeln("<info>New balance: {$balance->getPoints()} points</info>");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
```

**Register Command in `etc/di.xml`:**

```xml
<type name="Magento\Framework\Console\CommandList">
    <arguments>
        <argument name="commands" xsi:type="array">
            <item name="loyalty_add_points" xsi:type="object">Elsherif\LoyaltySystem\Console\Command\AddPointsCommand</item>
        </argument>
    </arguments>
</type>
```

---

### Step 8: Customer Data Sections

**File:** `etc/frontend/sections.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Invalidate customer data section when points change
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Customer:etc/sections.xsd">
    
    <action name="loyalty/customer/redeem">
        <section name="loyalty-points"/>
    </action>
    
    <action name="checkout/cart/add">
        <section name="loyalty-points"/>
    </action>
</config>
```

---

### Step 9: Email Templates

**File:** `view/frontend/email/points_earned.html`

```html
<!--
/**
 * Points Earned Email Template
 */
-->
{{template config_path="design/email/header_template"}}

<tr class="email-intro">
    <td>
        <p class="greeting">{{trans "Hello %name," name=$customer_name}}</p>
        <p>
            {{trans "Great news! You've earned <strong>%points loyalty points</strong> from your recent purchase." points=$points_earned}}
        </p>
    </td>
</tr>

<tr class="email-information">
    <td>
        <table class="message-info">
            <tr>
                <td><strong>{{trans "Points Earned:"}}</strong></td>
                <td>{{var points_earned}}</td>
            </tr>
            <tr>
                <td><strong>{{trans "New Balance:"}}</strong></td>
                <td>{{var new_balance}}</td>
            </tr>
        </table>
        
        <p>
            {{trans "You can use your points at checkout for discounts on future purchases!"}}
        </p>
        
        <div class="actions">
            <a href="{{store url='loyalty/customer/index'}}" class="action-button">
                {{trans "View Your Points"}}
            </a>
        </div>
    </td>
</tr>

{{template config_path="design/email/footer_template"}}
```

---

## ✅ Testing Phase 4

### Test Observer

```php
// Place a test order and verify points are added
// Check database: SELECT * FROM elsherif_points_transaction;
```

### Test Plugin

```bash
# Add item to cart
# Apply points in checkout
# Verify discount applied
```

### Test Cron

```bash
# Run manually
php bin/magento cron:run --group=default

# Or run the specific job
php bin/magento loyalty:points:expire
```

### Test CLI

```bash
# Add points
php bin/magento loyalty:points:add 1 100

# Expected output:
# Success! Added 100 points to customer 1
# New balance: 100 points
```

---

## 🎯 Phase 4 Complete!

✅ Event Observers  
✅ Plugins  
✅ Cron Jobs  
✅ CLI Commands  
✅ Email Templates  

**Next: Phase 5 - Frontend (Default Theme + PWA)**

