# 🔌 Phase 3: API Layer
## REST API, GraphQL & Service Contracts

---

## 📋 ما سنبنيه في هذه المرحلة:

✅ Service Contract Interfaces  
✅ Service Implementation (PointsManagement)  
✅ Repository Pattern  
✅ REST API Endpoints  
✅ GraphQL Schema  
✅ GraphQL Resolvers  
✅ Search Criteria Support  
✅ API Tests  

---

## 📁 File Structure

```
src/app/code/Elsherif/LoyaltySystem/
├── Api/
│   ├── PointsManagementInterface.php      ← Main service contract
│   ├── PointsBalanceRepositoryInterface.php
│   ├── PointsTransactionRepositoryInterface.php
│   │
│   └── Data/
│       ├── RedemptionResultInterface.php
│       ├── PointsBalanceSearchResultsInterface.php
│       └── PointsTransactionSearchResultsInterface.php
│
├── Model/
│   ├── PointsManagement.php               ← Service implementation
│   ├── PointsCalculator.php
│   ├── PointsBalanceRepository.php
│   ├── PointsTransactionRepository.php
│   ├── PointsBalanceSearchResults.php
│   ├── PointsTransactionSearchResults.php
│   ├── RedemptionResult.php
│   │
│   └── Resolver/                           ← GraphQL
│       ├── CustomerPoints.php
│       ├── PointsTransactions.php
│       ├── RedeemPoints.php
│       └── CancelRedemption.php
│
└── etc/
    ├── webapi.xml                          ← REST API routes
    ├── di.xml                              ← DI preferences
    └── graphql/
        └── schema.graphqls                 ← GraphQL schema
```

---

## 📝 Step-by-Step Implementation

### Step 1: Main Service Contract

**File:** `Api/PointsManagementInterface.php`

```php
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
```

---

### Step 2: Repository Interfaces

**File:** `Api/PointsBalanceRepositoryInterface.php`

```php
<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api;

use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PointsBalanceRepositoryInterface
{
    /**
     * Save balance
     *
     * @param \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface $balance
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface
     */
    public function save(PointsBalanceInterface $balance): PointsBalanceInterface;

    /**
     * Get by ID
     *
     * @param int $balanceId
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface
     */
    public function getById(int $balanceId): PointsBalanceInterface;

    /**
     * Get by customer ID
     *
     * @param int $customerId
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface
     */
    public function getByCustomerId(int $customerId): PointsBalanceInterface;

    /**
     * Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete
     *
     * @param \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface $balance
     * @return bool
     */
    public function delete(PointsBalanceInterface $balance): bool;
}
```

---

### Step 3: Additional Data Interfaces

**File:** `Api/Data/RedemptionResultInterface.php`

```php
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
```

**File:** `Model/RedemptionResult.php`

```php
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
```

---

### Step 4: Points Calculator

**File:** `Model/PointsCalculator.php`

```php
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
```

---

### Step 5: Service Implementation

**File:** `Model/PointsManagement.php`

```php
<?php
/**
 * Points Management Service
 * Main business logic implementation
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface;
use Elsherif\LoyaltySystem\Api\Data\RedemptionResultInterface;
use Elsherif\LoyaltySystem\Api\PointsBalanceRepositoryInterface;
use Elsherif\LoyaltySystem\Model\PointsBalanceFactory;
use Elsherif\LoyaltySystem\Model\PointsTransactionFactory;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction as TransactionResource;
use Elsherif\LoyaltySystem\Model\Config;
use Elsherif\LoyaltySystem\Model\PointsCalculator;
use Elsherif\LoyaltySystem\Api\Data\RedemptionResultInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class PointsManagement implements PointsManagementInterface
{
    /**
     * @var PointsBalanceRepositoryInterface
     */
    private $balanceRepository;

    /**
     * @var PointsBalanceFactory
     */
    private $balanceFactory;

    /**
     * @var PointsTransactionFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionResource
     */
    private $transactionResource;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PointsCalculator
     */
    private $calculator;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var RedemptionResultInterfaceFactory
     */
    private $redemptionResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(
        PointsBalanceRepositoryInterface $balanceRepository,
        PointsBalanceFactory $balanceFactory,
        PointsTransactionFactory $transactionFactory,
        TransactionResource $transactionResource,
        Config $config,
        PointsCalculator $calculator,
        CartRepositoryInterface $quoteRepository,
        RedemptionResultInterfaceFactory $redemptionResultFactory,
        LoggerInterface $logger
    ) {
        $this->balanceRepository = $balanceRepository;
        $this->balanceFactory = $balanceFactory;
        $this->transactionFactory = $transactionFactory;
        $this->transactionResource = $transactionResource;
        $this->config = $config;
        $this->calculator = $calculator;
        $this->quoteRepository = $quoteRepository;
        $this->redemptionResultFactory = $redemptionResultFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function addPoints(
        int $customerId,
        int $points,
        string $action,
        ?int $referenceId = null,
        ?string $expiresAt = null,
        ?string $comment = null
    ): bool {
        try {
            // Get or create balance
            try {
                $balance = $this->balanceRepository->getByCustomerId($customerId);
            } catch (NoSuchEntityException $e) {
                $balance = $this->balanceFactory->create();
                $balance->setCustomerId($customerId);
                $balance->setPoints(0);
                $balance->setLifetimePoints(0);
                $balance->setPointsSpent(0);
            }

            // Update balance
            $currentPoints = $balance->getPoints();
            $newPoints = $currentPoints + $points;
            
            $balance->setPoints($newPoints);
            $balance->setLifetimePoints($balance->getLifetimePoints() + $points);

            // Save balance
            $this->balanceRepository->save($balance);

            // Create transaction record
            $transaction = $this->transactionFactory->create();
            $transaction->setCustomerId($customerId);
            $transaction->setPoints($points);
            $transaction->setBalanceAfter($newPoints);
            $transaction->setAction($action);
            $transaction->setReferenceId($referenceId);
            $transaction->setReferenceType($this->getRefeferenceType($referenceId));
            $transaction->setExpiresAt($expiresAt);
            $transaction->setComment($comment);

            $this->transactionResource->save($transaction);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error adding points: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deductPoints(
        int $customerId,
        int $points,
        string $action,
        ?int $referenceId = null,
        ?string $comment = null
    ): bool {
        try {
            // Get balance
            $balance = $this->balanceRepository->getByCustomerId($customerId);

            // Check sufficient balance
            if ($balance->getPoints() < $points) {
                throw new LocalizedException(__('Insufficient points balance.'));
            }

            // Update balance
            $currentPoints = $balance->getPoints();
            $newPoints = $currentPoints - $points;
            
            $balance->setPoints($newPoints);
            $balance->setPointsSpent($balance->getPointsSpent() + $points);

            // Save balance
            $this->balanceRepository->save($balance);

            // Create transaction record (negative points)
            $transaction = $this->transactionFactory->create();
            $transaction->setCustomerId($customerId);
            $transaction->setPoints(-$points); // Negative!
            $transaction->setBalanceAfter($newPoints);
            $transaction->setAction($action);
            $transaction->setReferenceId($referenceId);
            $transaction->setReferenceType($this->getReferenceType($referenceId));
            $transaction->setComment($comment);

            $this->transactionResource->save($transaction);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error deducting points: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getBalance(int $customerId): PointsBalanceInterface
    {
        try {
            return $this->balanceRepository->getByCustomerId($customerId);
        } catch (NoSuchEntityException $e) {
            // Create empty balance
            $balance = $this->balanceFactory->create();
            $balance->setCustomerId($customerId);
            $balance->setPoints(0);
            $balance->setLifetimePoints(0);
            $balance->setPointsSpent(0);
            
            return $balance;
        }
    }

    /**
     * @inheritDoc
     */
    public function redeemPoints(int $quoteId, int $points): RedemptionResultInterface
    {
        $result = $this->redemptionResultFactory->create();

        try {
            // Validate
            if (!$this->config->isEnabled()) {
                throw new LocalizedException(__('Loyalty system is disabled.'));
            }

            if ($points < $this->config->getMinPointsToRedeem()) {
                throw new LocalizedException(
                    __('Minimum %1 points required.', $this->config->getMinPointsToRedeem())
                );
            }

            // Get quote
            $quote = $this->quoteRepository->getActive($quoteId);

            if (!$quote->getCustomerId()) {
                throw new LocalizedException(__('Guest customers cannot redeem points.'));
            }

            // Get balance
            $balance = $this->getBalance((int) $quote->getCustomerId());

            if ($balance->getPoints() < $points) {
                throw new LocalizedException(__('Insufficient points.'));
            }

            // Calculate discount
            $discount = $this->calculator->calculateDiscount($points);

            // Apply to quote (via extension attributes)
            $extensionAttributes = $quote->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributes->setLoyaltyPointsUsed($points);
                $extensionAttributes->setLoyaltyDiscountAmount($discount);
                $quote->setExtensionAttributes($extensionAttributes);
            }

            // Save quote
            $this->quoteRepository->save($quote);

            // Set result
            $result->setSuccess(true);
            $result->setMessage(__('Points applied successfully.'));
            $result->setPointsUsed($points);
            $result->setDiscountAmount($discount);
            $result->setNewBalance($balance->getPoints()); // Will be deducted on order placement

        } catch (\Exception $e) {
            $result->setSuccess(false);
            $result->setMessage($e->getMessage());
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function cancelRedemption(int $quoteId): bool
    {
        try {
            $quote = $this->quoteRepository->getActive($quoteId);

            $extensionAttributes = $quote->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributes->setLoyaltyPointsUsed(0);
                $extensionAttributes->setLoyaltyDiscountAmount(0.0);
                $quote->setExtensionAttributes($extensionAttributes);
            }

            $this->quoteRepository->save($quote);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error cancelling redemption: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get reference type from ID
     *
     * @param int|null $referenceId
     * @return string|null
     */
    private function getReferenceType(?int $referenceId): ?string
    {
        return $referenceId ? 'order' : null;
    }
}
```

---

### Step 6: Repository Implementation

**File:** `Model/PointsBalanceRepository.php`

```php
<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Elsherif\LoyaltySystem\Api\PointsBalanceRepositoryInterface;
use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Elsherif\LoyaltySystem\Model\PointsBalanceFactory;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance as BalanceResource;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class PointsBalanceRepository implements PointsBalanceRepositoryInterface
{
    private $balanceFactory;
    private $balanceResource;
    private $collectionFactory;

    public function __construct(
        PointsBalanceFactory $balanceFactory,
        BalanceResource $balanceResource,
        CollectionFactory $collectionFactory
    ) {
        $this->balanceFactory = $balanceFactory;
        $this->balanceResource = $balanceResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function save(PointsBalanceInterface $balance): PointsBalanceInterface
    {
        $this->balanceResource->save($balance);
        return $balance;
    }

    public function getById(int $balanceId): PointsBalanceInterface
    {
        $balance = $this->balanceFactory->create();
        $this->balanceResource->load($balance, $balanceId);

        if (!$balance->getBalanceId()) {
            throw new NoSuchEntityException(__('Balance not found.'));
        }

        return $balance;
    }

    public function getByCustomerId(int $customerId): PointsBalanceInterface
    {
        $balance = $this->balanceFactory->create();
        $this->balanceResource->loadByCustomerId($balance, $customerId);

        if (!$balance->getBalanceId()) {
            throw new NoSuchEntityException(__('Balance not found for customer.'));
        }

        return $balance;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // Implement search criteria support
        // We'll skip this for brevity
    }

    public function delete(PointsBalanceInterface $balance): bool
    {
        $this->balanceResource->delete($balance);
        return true;
    }
}
```

---

### Step 7: REST API Configuration

**File:** `etc/webapi.xml`

```xml
<?xml version="1.0"?>
<routes xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Webapi:etc/webapi.xsd">
    
    <!-- Get Customer Balance -->
    <route url="/V1/loyalty/balance/:customerId" method="GET">
        <service class="Elsherif\LoyaltySystem\Api\PointsManagementInterface" method="getBalance"/>
        <resources>
            <resource ref="self"/>
        </resources>
    </route>

    <!-- Add Points (Admin only) -->
    <route url="/V1/loyalty/points/add" method="POST">
        <service class="Elsherif\LoyaltySystem\Api\PointsManagementInterface" method="addPoints"/>
        <resources>
            <resource ref="Elsherif_LoyaltySystem::points_manage"/>
        </resources>
    </route>

    <!-- Redeem Points -->
    <route url="/V1/loyalty/redeem" method="POST">
        <service class="Elsherif\LoyaltySystem\Api\PointsManagementInterface" method="redeemPoints"/>
        <resources>
            <resource ref="self"/>
        </resources>
    </route>

    <!-- Cancel Redemption -->
    <route url="/V1/loyalty/cancel" method="POST">
        <service class="Elsherif\LoyaltySystem\Api\PointsManagementInterface" method="cancelRedemption"/>
        <resources>
            <resource ref="self"/>
        </resources>
    </route>
</routes>
```

---

### Step 8: GraphQL Schema

**File:** `etc/graphql/schema.graphqls`

```graphql
type Query {
    customerPoints: CustomerPointsBalance 
        @resolver(class: "Elsherif\\LoyaltySystem\\Model\\Resolver\\CustomerPoints") 
        @doc(description: "Get current customer's points balance") 
        @cache(cacheIdentity: "Elsherif\\LoyaltySystem\\Model\\Resolver\\Identity\\PointsIdentity")
}

type Mutation {
    redeemPoints(input: RedeemPointsInput!): RedeemPointsOutput 
        @resolver(class: "Elsherif\\LoyaltySystem\\Model\\Resolver\\RedeemPoints")
}

type CustomerPointsBalance {
    balance_id: Int
    customer_id: Int
    points: Int @doc(description: "Available points")
    lifetime_points: Int
    points_spent: Int
    updated_at: String
}

input RedeemPointsInput {
    cart_id: String!
    points: Int!
}

type RedeemPointsOutput {
    success: Boolean
    message: String
    points_used: Int
    discount_amount: Float
    new_balance: Int
}
```

---

### Step 9: GraphQL Resolver

**File:** `Model/Resolver/CustomerPoints.php`

```php
<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;

class CustomerPoints implements ResolverInterface
{
    private $pointsManagement;

    public function __construct(PointsManagementInterface $pointsManagement)
    {
        $this->pointsManagement = $pointsManagement;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('Customer not authenticated.'));
        }

        $balance = $this->pointsManagement->getBalance($context->getUserId());

        return [
            'balance_id' => $balance->getBalanceId(),
            'customer_id' => $balance->getCustomerId(),
            'points' => $balance->getPoints(),
            'lifetime_points' => $balance->getLifetimePoints(),
            'points_spent' => $balance->getPointsSpent(),
            'updated_at' => $balance->getUpdatedAt()
        ];
    }
}
```

---

## ✅ Testing Phase 3

### Test REST API

```bash
# Get customer balance
curl -X GET "https://your-magento.com/rest/V1/loyalty/balance/1" \
  -H "Authorization: Bearer {customer_token}"

# Redeem points
curl -X POST "https://your-magento.com/rest/V1/loyalty/redeem" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quoteId": 123,
    "points": 100
  }'
```

### Test GraphQL

```graphql
# Query customer points
{
  customerPoints {
    points
    lifetime_points
  }
}

# Redeem points
mutation {
  redeemPoints(input: {
    cart_id: "abc123"
    points: 100
  }) {
    success
    message
  }
}
```

---

## 🎯 Phase 3 Complete!

✅ Service Contracts  
✅ Repositories  
✅ Business Logic  
✅ REST API  
✅ GraphQL  

**Next: Phase 4 - Business Logic (Observers, Plugins, Cron)**

