# 💾 Phase 2: Database & Models Layer
## Database Schema, Models, ResourceModels & Repositories

---

## 📋 ما سنبنيه في هذه المرحلة:

✅ Database Schema (Tables)  
✅ Model Classes  
✅ ResourceModel Classes  
✅ Collection Classes  
✅ Data Interfaces (API)  
✅ Repository Classes  
✅ Search Results  
✅ Extension Attributes  

---

## 📁 File Structure

```
src/app/code/Elsherif/LoyaltySystem/
├── etc/
│   ├── db_schema.xml              ← Database schema
│   ├── db_schema_whitelist.json   ← Auto-generated
│   └── extension_attributes.xml   ← Quote/Order extensions
│
├── Api/
│   └── Data/
│       ├── PointsBalanceInterface.php
│       ├── PointsTransactionInterface.php
│       ├── PointsBalanceSearchResultsInterface.php
│       └── PointsTransactionSearchResultsInterface.php
│
├── Model/
│   ├── PointsBalance.php
│   ├── PointsTransaction.php
│   ├── PointsBalanceSearchResults.php
│   ├── PointsTransactionSearchResults.php
│   │
│   └── ResourceModel/
│       ├── PointsBalance.php
│       ├── PointsTransaction.php
│       │
│       └── PointsBalance/
│           └── Collection.php
│       └── PointsTransaction/
│           └── Collection.php
│
└── Setup/
    └── Patch/
        └── Data/
            └── AddSamplePoints.php (optional)
```

---

## 📝 Step-by-Step Implementation

### Step 1: Database Schema

**File:** `etc/db_schema.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Database Schema
 * يُنشئ جداول loyalty points
 */
-->
<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd">
    
    <!-- Table 1: Points Balance -->
    <table name="elsherif_points_balance" resource="default" engine="innodb" 
           comment="Loyalty Points Balance">
        
        <!-- Primary Key -->
        <column xsi:type="int" name="balance_id" unsigned="true" nullable="false" 
                identity="true" comment="Balance ID"/>
        
        <!-- Foreign Key to Customer -->
        <column xsi:type="int" name="customer_id" unsigned="true" nullable="false" 
                comment="Customer ID"/>
        
        <!-- Points Data -->
        <column xsi:type="int" name="points" unsigned="false" nullable="false" 
                default="0" comment="Current Points"/>
        
        <column xsi:type="int" name="lifetime_points" unsigned="false" nullable="false" 
                default="0" comment="Total Points Ever Earned"/>
        
        <column xsi:type="int" name="points_spent" unsigned="false" nullable="false" 
                default="0" comment="Total Points Spent"/>
        
        <!-- Timestamp -->
        <column xsi:type="timestamp" name="updated_at" nullable="false" 
                default="CURRENT_TIMESTAMP" on_update="true" 
                comment="Last Updated At"/>
        
        <!-- Constraints -->
        <constraint xsi:type="primary" referenceId="PRIMARY">
            <column name="balance_id"/>
        </constraint>
        
        <constraint xsi:type="unique" referenceId="ELSHERIF_POINTS_BALANCE_CUSTOMER_ID">
            <column name="customer_id"/>
        </constraint>
        
        <constraint xsi:type="foreign" referenceId="ELSHERIF_POINTS_BALANCE_CUSTOMER_ID_CUSTOMER_ENTITY_ENTITY_ID"
                    table="elsherif_points_balance" column="customer_id"
                    referenceTable="customer_entity" referenceColumn="entity_id"
                    onDelete="CASCADE"/>
        
        <!-- Indexes -->
        <index referenceId="ELSHERIF_POINTS_BALANCE_CUSTOMER_ID" indexType="btree">
            <column name="customer_id"/>
        </index>
        
        <index referenceId="ELSHERIF_POINTS_BALANCE_POINTS" indexType="btree">
            <column name="points"/>
        </index>
    </table>
    
    
    <!-- Table 2: Points Transaction -->
    <table name="elsherif_points_transaction" resource="default" engine="innodb" 
           comment="Loyalty Points Transactions">
        
        <!-- Primary Key -->
        <column xsi:type="int" name="transaction_id" unsigned="true" nullable="false" 
                identity="true" comment="Transaction ID"/>
        
        <!-- Foreign Key to Customer -->
        <column xsi:type="int" name="customer_id" unsigned="true" nullable="false" 
                comment="Customer ID"/>
        
        <!-- Transaction Data -->
        <column xsi:type="int" name="points" unsigned="false" nullable="false" 
                comment="Points (+ve = earned, -ve = spent/expired)"/>
        
        <column xsi:type="int" name="balance_after" unsigned="false" nullable="false" 
                default="0" comment="Balance After Transaction"/>
        
        <column xsi:type="varchar" name="action" nullable="false" length="50" 
                comment="Action Type (order_complete, redemption, expired, admin_adjust, refund)"/>
        
        <!-- Reference Data -->
        <column xsi:type="int" name="reference_id" unsigned="true" nullable="true" 
                comment="Reference ID (order_id, etc.)"/>
        
        <column xsi:type="varchar" name="reference_type" nullable="true" length="50" 
                comment="Reference Type (order, adjustment, manual)"/>
        
        <!-- Expiration -->
        <column xsi:type="datetime" name="expires_at" nullable="true" 
                comment="Expiration Date"/>
        
        <!-- Comment -->
        <column xsi:type="varchar" name="comment" nullable="true" length="255" 
                comment="Transaction Comment"/>
        
        <!-- Timestamp -->
        <column xsi:type="timestamp" name="created_at" nullable="false" 
                default="CURRENT_TIMESTAMP" comment="Created At"/>
        
        <!-- Constraints -->
        <constraint xsi:type="primary" referenceId="PRIMARY">
            <column name="transaction_id"/>
        </constraint>
        
        <constraint xsi:type="foreign" referenceId="ELSHERIF_POINTS_TRANSACTION_CUSTOMER_ID_CUSTOMER_ENTITY_ENTITY_ID"
                    table="elsherif_points_transaction" column="customer_id"
                    referenceTable="customer_entity" referenceColumn="entity_id"
                    onDelete="CASCADE"/>
        
        <!-- Indexes -->
        <index referenceId="ELSHERIF_POINTS_TRANSACTION_CUSTOMER_ID" indexType="btree">
            <column name="customer_id"/>
        </index>
        
        <index referenceId="ELSHERIF_POINTS_TRANSACTION_ACTION" indexType="btree">
            <column name="action"/>
        </index>
        
        <index referenceId="ELSHERIF_POINTS_TRANSACTION_EXPIRES_AT" indexType="btree">
            <column name="expires_at"/>
        </index>
        
        <index referenceId="ELSHERIF_POINTS_TRANSACTION_REFERENCE" indexType="btree">
            <column name="reference_type"/>
            <column name="reference_id"/>
        </index>
    </table>
</schema>
```

---

### Step 2: Data Interfaces (API Contracts)

#### 2.1 Points Balance Interface

**File:** `Api/Data/PointsBalanceInterface.php`

```php
<?php
/**
 * Points Balance Data Interface
 * Service Contract for Points Balance
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api\Data;

interface PointsBalanceInterface
{
    /**
     * Constants for keys
     */
    const BALANCE_ID = 'balance_id';
    const CUSTOMER_ID = 'customer_id';
    const POINTS = 'points';
    const LIFETIME_POINTS = 'lifetime_points';
    const POINTS_SPENT = 'points_spent';
    const UPDATED_AT = 'updated_at';

    /**
     * Get balance ID
     *
     * @return int|null
     */
    public function getBalanceId(): ?int;

    /**
     * Set balance ID
     *
     * @param int $balanceId
     * @return $this
     */
    public function setBalanceId(int $balanceId): self;

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * Get current points
     *
     * @return int
     */
    public function getPoints(): int;

    /**
     * Set current points
     *
     * @param int $points
     * @return $this
     */
    public function setPoints(int $points): self;

    /**
     * Get lifetime points
     *
     * @return int
     */
    public function getLifetimePoints(): int;

    /**
     * Set lifetime points
     *
     * @param int $lifetimePoints
     * @return $this
     */
    public function setLifetimePoints(int $lifetimePoints): self;

    /**
     * Get points spent
     *
     * @return int
     */
    public function getPointsSpent(): int;

    /**
     * Set points spent
     *
     * @param int $pointsSpent
     * @return $this
     */
    public function setPointsSpent(int $pointsSpent): self;

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): self;
}
```

---

#### 2.2 Points Transaction Interface

**File:** `Api/Data/PointsTransactionInterface.php`

```php
<?php
/**
 * Points Transaction Data Interface
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api\Data;

interface PointsTransactionInterface
{
    /**
     * Constants for keys
     */
    const TRANSACTION_ID = 'transaction_id';
    const CUSTOMER_ID = 'customer_id';
    const POINTS = 'points';
    const BALANCE_AFTER = 'balance_after';
    const ACTION = 'action';
    const REFERENCE_ID = 'reference_id';
    const REFERENCE_TYPE = 'reference_type';
    const EXPIRES_AT = 'expires_at';
    const COMMENT = 'comment';
    const CREATED_AT = 'created_at';

    /**
     * Action Types
     */
    const ACTION_ORDER_COMPLETE = 'order_complete';
    const ACTION_REDEMPTION = 'redemption';
    const ACTION_EXPIRED = 'expired';
    const ACTION_ADMIN_ADJUST = 'admin_adjust';
    const ACTION_REFUND = 'refund';

    /**
     * Get transaction ID
     *
     * @return int|null
     */
    public function getTransactionId(): ?int;

    /**
     * Set transaction ID
     *
     * @param int $transactionId
     * @return $this
     */
    public function setTransactionId(int $transactionId): self;

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * Get points
     *
     * @return int
     */
    public function getPoints(): int;

    /**
     * Set points
     *
     * @param int $points
     * @return $this
     */
    public function setPoints(int $points): self;

    /**
     * Get balance after transaction
     *
     * @return int
     */
    public function getBalanceAfter(): int;

    /**
     * Set balance after transaction
     *
     * @param int $balanceAfter
     * @return $this
     */
    public function setBalanceAfter(int $balanceAfter): self;

    /**
     * Get action
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Set action
     *
     * @param string $action
     * @return $this
     */
    public function setAction(string $action): self;

    /**
     * Get reference ID
     *
     * @return int|null
     */
    public function getReferenceId(): ?int;

    /**
     * Set reference ID
     *
     * @param int|null $referenceId
     * @return $this
     */
    public function setReferenceId(?int $referenceId): self;

    /**
     * Get reference type
     *
     * @return string|null
     */
    public function getReferenceType(): ?string;

    /**
     * Set reference type
     *
     * @param string|null $referenceType
     * @return $this
     */
    public function setReferenceType(?string $referenceType): self;

    /**
     * Get expires at
     *
     * @return string|null
     */
    public function getExpiresAt(): ?string;

    /**
     * Set expires at
     *
     * @param string|null $expiresAt
     * @return $this
     */
    public function setExpiresAt(?string $expiresAt): self;

    /**
     * Get comment
     *
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * Set comment
     *
     * @param string|null $comment
     * @return $this
     */
    public function setComment(?string $comment): self;

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;
}
```

---

### Step 3: Model Classes

#### 3.1 PointsBalance Model

**File:** `Model/PointsBalance.php`

```php
<?php
/**
 * Points Balance Model
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Magento\Framework\Model\AbstractModel;
use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance as PointsBalanceResource;

class PointsBalance extends AbstractModel implements PointsBalanceInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'elsherif_loyalty_points_balance';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PointsBalanceResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getBalanceId(): ?int
    {
        return $this->getData(self::BALANCE_ID) 
            ? (int) $this->getData(self::BALANCE_ID) 
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setBalanceId(int $balanceId): PointsBalanceInterface
    {
        return $this->setData(self::BALANCE_ID, $balanceId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): PointsBalanceInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getPoints(): int
    {
        return (int) $this->getData(self::POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setPoints(int $points): PointsBalanceInterface
    {
        return $this->setData(self::POINTS, $points);
    }

    /**
     * @inheritDoc
     */
    public function getLifetimePoints(): int
    {
        return (int) $this->getData(self::LIFETIME_POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setLifetimePoints(int $lifetimePoints): PointsBalanceInterface
    {
        return $this->setData(self::LIFETIME_POINTS, $lifetimePoints);
    }

    /**
     * @inheritDoc
     */
    public function getPointsSpent(): int
    {
        return (int) $this->getData(self::POINTS_SPENT);
    }

    /**
     * @inheritDoc
     */
    public function setPointsSpent(int $pointsSpent): PointsBalanceInterface
    {
        return $this->setData(self::POINTS_SPENT, $pointsSpent);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt): PointsBalanceInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
```

---

#### 3.2 PointsTransaction Model

**File:** `Model/PointsTransaction.php`

```php
<?php
/**
 * Points Transaction Model
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Magento\Framework\Model\AbstractModel;
use Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction as PointsTransactionResource;

class PointsTransaction extends AbstractModel implements PointsTransactionInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'elsherif_loyalty_points_transaction';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PointsTransactionResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId(): ?int
    {
        return $this->getData(self::TRANSACTION_ID) 
            ? (int) $this->getData(self::TRANSACTION_ID) 
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId(int $transactionId): PointsTransactionInterface
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): PointsTransactionInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getPoints(): int
    {
        return (int) $this->getData(self::POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setPoints(int $points): PointsTransactionInterface
    {
        return $this->setData(self::POINTS, $points);
    }

    /**
     * @inheritDoc
     */
    public function getBalanceAfter(): int
    {
        return (int) $this->getData(self::BALANCE_AFTER);
    }

    /**
     * @inheritDoc
     */
    public function setBalanceAfter(int $balanceAfter): PointsTransactionInterface
    {
        return $this->setData(self::BALANCE_AFTER, $balanceAfter);
    }

    /**
     * @inheritDoc
     */
    public function getAction(): string
    {
        return (string) $this->getData(self::ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setAction(string $action): PointsTransactionInterface
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * @inheritDoc
     */
    public function getReferenceId(): ?int
    {
        return $this->getData(self::REFERENCE_ID) 
            ? (int) $this->getData(self::REFERENCE_ID) 
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setReferenceId(?int $referenceId): PointsTransactionInterface
    {
        return $this->setData(self::REFERENCE_ID, $referenceId);
    }

    /**
     * @inheritDoc
     */
    public function getReferenceType(): ?string
    {
        return $this->getData(self::REFERENCE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setReferenceType(?string $referenceType): PointsTransactionInterface
    {
        return $this->setData(self::REFERENCE_TYPE, $referenceType);
    }

    /**
     * @inheritDoc
     */
    public function getExpiresAt(): ?string
    {
        return $this->getData(self::EXPIRES_AT);
    }

    /**
     * @inheritDoc
     */
    public function setExpiresAt(?string $expiresAt): PointsTransactionInterface
    {
        return $this->setData(self::EXPIRES_AT, $expiresAt);
    }

    /**
     * @inheritDoc
     */
    public function getComment(): ?string
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * @inheritDoc
     */
    public function setComment(?string $comment): PointsTransactionInterface
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): PointsTransactionInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
```

---

### Step 4: ResourceModel Classes

#### 4.1 PointsBalance ResourceModel

**File:** `Model/ResourceModel/PointsBalance.php`

```php
<?php
/**
 * Points Balance Resource Model
 * Database operations
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PointsBalance extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('elsherif_points_balance', 'balance_id');
    }

    /**
     * Load balance by customer ID
     *
     * @param \Elsherif\LoyaltySystem\Model\PointsBalance $object
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomerId($object, int $customerId)
    {
        $connection = $this->getConnection();
        $bind = ['customer_id' => $customerId];
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('customer_id = :customer_id');

        $data = $connection->fetchRow($select, $bind);

        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }
}
```

---

#### 4.2 PointsBalance Collection

**File:** `Model/ResourceModel/PointsBalance/Collection.php`

```php
<?php
/**
 * Points Balance Collection
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Elsherif\LoyaltySystem\Model\PointsBalance;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance as PointsBalanceResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'balance_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            PointsBalance::class,
            PointsBalanceResource::class
        );
    }
}
```

---

#### 4.3 PointsTransaction ResourceModel

**File:** `Model/ResourceModel/PointsTransaction.php`

```php
<?php
/**
 * Points Transaction Resource Model
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PointsTransaction extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('elsherif_points_transaction', 'transaction_id');
    }
}
```

---

#### 4.4 PointsTransaction Collection

**File:** `Model/ResourceModel/PointsTransaction/Collection.php`

```php
<?php
/**
 * Points Transaction Collection
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Elsherif\LoyaltySystem\Model\PointsTransaction;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction as PointsTransactionResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            PointsTransaction::class,
            PointsTransactionResource::class
        );
    }

    /**
     * Filter by customer
     *
     * @param int $customerId
     * @return $this
     */
    public function filterByCustomer(int $customerId): self
    {
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    /**
     * Filter by action
     *
     * @param string $action
     * @return $this
     */
    public function filterByAction(string $action): self
    {
        $this->addFieldToFilter('action', $action);
        return $this;
    }

    /**
     * Filter expired transactions
     *
     * @return $this
     */
    public function filterExpired(): self
    {
        $this->addFieldToFilter('expires_at', [
            'notnull' => true
        ]);
        $this->addFieldToFilter('expires_at', [
            'lt' => new \Zend_Db_Expr('NOW()')
        ]);
        return $this;
    }

    /**
     * Order by creation date
     *
     * @param string $dir
     * @return $this
     */
    public function orderByCreatedAt(string $dir = 'DESC'): self
    {
        $this->setOrder('created_at', $dir);
        return $this;
    }
}
```

---

### Step 5: Extension Attributes (Quote & Order)

**File:** `etc/extension_attributes.xml`

```xml
<?xml version="1.0"?>
<!--
/**
 * Extension Attributes
 * إضافة حقول للـ Quote و Order
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Api/etc/extension_attributes.xsd">
    
    <!-- Quote Extension Attributes -->
    <extension_attributes for="Magento\Quote\Api\Data\CartInterface">
        <attribute code="loyalty_points_used" type="int">
            <comment>Number of loyalty points used in this quote</comment>
        </attribute>
        <attribute code="loyalty_discount_amount" type="float">
            <comment>Discount amount from loyalty points</comment>
        </attribute>
    </extension_attributes>
    
    <!-- Order Extension Attributes -->
    <extension_attributes for="Magento\Sales\Api\Data\OrderInterface">
        <attribute code="loyalty_points_earned" type="int">
            <comment>Loyalty points earned from this order</comment>
        </attribute>
        <attribute code="loyalty_points_used" type="int">
            <comment>Loyalty points used in this order</comment>
        </attribute>
        <attribute code="loyalty_discount_amount" type="float">
            <comment>Discount amount from loyalty points</comment>
        </attribute>
    </extension_attributes>
</config>
```

---

### Step 6: Update di.xml with Preferences

**File:** `etc/di.xml` (إضافة)

```xml
<!-- في etc/di.xml، أضف هذه السطور -->

<!-- Data Interfaces Preferences -->
<preference for="Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface"
            type="Elsherif\LoyaltySystem\Model\PointsBalance"/>

<preference for="Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface"
            type="Elsherif\LoyaltySystem\Model\PointsTransaction"/>
```

---

## ✅ Testing Phase 2

### Apply Database Schema

```bash
# Run setup upgrade to create tables
php bin/magento setup:upgrade

# Generate whitelist (للسماح بالتعديلات المستقبلية)
php bin/magento setup:db-declaration:generate-whitelist --module-name=Elsherif_LoyaltySystem

# Verify tables exist
php bin/magento db:status
```

### Verify Tables in Database

```sql
-- Connect to your MySQL database
mysql -u root -p

USE magento_database;

-- Check tables
SHOW TABLES LIKE 'elsherif_%';

-- Check balance table structure
DESCRIBE elsherif_points_balance;

-- Check transaction table structure
DESCRIBE elsherif_points_transaction;
```

**Expected Output:**
```
+----------------------------+
| Tables_in_magento          |
+----------------------------+
| elsherif_points_balance    |
| elsherif_points_transaction|
+----------------------------+
```

### Test Model (Quick PHP Test)

```php
<?php
// في bin/magento أو setup script

use Elsherif\LoyaltySystem\Model\PointsBalanceFactory;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance as PointsBalanceResource;

// Object Manager (للاختبار فقط - لا تستخدمه في Production)
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

$balanceFactory = $objectManager->get(PointsBalanceFactory::class);
$balanceResource = $objectManager->get(PointsBalanceResource::class);

// Create new balance
$balance = $balanceFactory->create();
$balance->setCustomerId(1); // Customer ID 1
$balance->setPoints(100);
$balance->setLifetimePoints(100);
$balance->setPointsSpent(0);

$balanceResource->save($balance);

echo "Balance created with ID: " . $balance->getBalanceId();
```

---

## 🎯 ما تم إنجازه في Phase 2:

✅ Database Schema (2 Tables)  
✅ Data Interfaces (API Contracts)  
✅ Model Classes (PointsBalance, PointsTransaction)  
✅ ResourceModel Classes  
✅ Collection Classes  
✅ Extension Attributes (Quote & Order)  
✅ Custom Collection Methods  

---

## 📚 Key Concepts Learned:

1. **Declarative Schema** - `db_schema.xml` بدلاً من InstallSchema
2. **Data Interfaces** - Service Contracts
3. **Models** - Business entities
4. **ResourceModels** - Database operations
5. **Collections** - Query multiple records
6. **Extension Attributes** - Extend existing entities

---

## 🚀 Next Phase

**Phase 3: API Layer (REST + GraphQL)**
- REST API Endpoints
- GraphQL Schema & Resolvers
- Service Contracts Implementation
- Repositories

---

**Ready for Phase 3? 🚀**
