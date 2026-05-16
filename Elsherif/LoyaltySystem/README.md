# 🎁 Elsherif Loyalty System - نظام الولاء الشامل

## 📋 Table of Contents
- [نظرة عامة](#نظرة-عامة)
- [المفاهيم الأساسية](#المفاهيم-الأساسية)
- [البنية المعمارية (Architecture)](#البنية-المعمارية-architecture)
- [اللوجيك والمنطق](#اللوجيك-والمنطق)
- [هيكل الموديول](#هيكل-الموديول)
- [Database Schema](#database-schema)
- [API Documentation](#api-documentation)
- [GraphQL Schema](#graphql-schema)
- [Frontend Integration](#frontend-integration)
- [Installation](#installation)
- [Configuration](#configuration)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

---

## 🎯 نظرة عامة

### ما هو Loyalty System؟
نظام ولاء متكامل يسمح للعملاء بـ:
- **كسب نقاط** عند كل عملية شراء
- **تتبع رصيد النقاط** في الحساب الخاص بهم
- **استبدال النقاط** كخصم في عملية الشراء
- **عرض سجل المعاملات** لكل النقاط المكتسبة/المستخدمة
- **انتهاء صلاحية النقاط** بعد فترة زمنية محددة

### الميزات الرئيسية
✅ دعم كامل لـ **Default Theme** و **PWA Studio**  
✅ **REST API** و **GraphQL** للتكامل مع أي Frontend  
✅ **Cron Jobs** لانتهاء صلاحية النقاط تلقائياً  
✅ **Email Notifications** عند كسب النقاط  
✅ **Admin Dashboard** لإدارة قواعد النقاط  
✅ **Multi-Store** Support  
✅ **Extensible** - قابل للتوسع بسهولة  

---

## 💡 المفاهيم الأساسية

### 1. كسب النقاط (Earning Points)
```
قاعدة افتراضية: كل 10 جنيه = 1 نقطة
```

**مثال:**
- العميل اشترى بـ **500 جنيه**
- النقاط المكتسبة = `500 / 10 = 50 نقطة`
- يتم إضافتها لرصيده **فوراً** بعد إكمال الطلب

### 2. استبدال النقاط (Redeeming Points)
```
قاعدة افتراضية: 10 نقاط = 1 جنيه خصم
```

**مثال:**
- العميل لديه **200 نقطة**
- قيمة الخصم المتاحة = `200 / 10 = 20 جنيه`
- يمكنه استخدام جزء أو كل النقاط في الـ Checkout

### 3. انتهاء الصلاحية (Expiration)
```
افتراضي: النقاط تنتهي بعد 180 يوم
```

**مثال:**
- العميل كسب **100 نقطة** في `2025-01-01`
- تنتهي صلاحيتها في `2025-06-30`
- Cron Job يحذفها تلقائياً

### 4. أنواع المعاملات (Transaction Types)
| Type | Description | Points |
|------|-------------|--------|
| `order_complete` | كسب نقاط من طلب | `+ve` |
| `redemption` | استخدام نقاط | `-ve` |
| `expired` | انتهاء صلاحية | `-ve` |
| `admin_adjust` | تعديل من Admin | `+ve/-ve` |
| `refund` | إرجاع طلب | `-ve` |

---

## 🏗️ البنية المعمارية (Architecture)

### Design Principles

#### 1️⃣ Headless Architecture
```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND LAYER                            │
│  ┌────────────────────┐         ┌─────────────────────┐    │
│  │   Default Theme    │         │    PWA Studio       │    │
│  │   (Luma/Blank)     │         │    (React)          │    │
│  └────────┬───────────┘         └──────────┬──────────┘    │
│           │                                 │                │
│           │ REST API / GraphQL             │                │
└───────────┼─────────────────────────────────┼────────────────┘
            │                                 │
            ▼                                 ▼
┌─────────────────────────────────────────────────────────────┐
│                      API LAYER                               │
│  ┌──────────────────┐         ┌──────────────────────┐     │
│  │   REST API       │         │   GraphQL API        │     │
│  │   /V1/loyalty    │         │   customerPoints     │     │
│  └────────┬─────────┘         └──────────┬───────────┘     │
└───────────┼────────────────────────────────┼─────────────────┘
            │                                │
            └────────────┬───────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                   SERVICE LAYER                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  PointsManagementInterface (Service Contract)        │   │
│  │  - addPoints()                                        │   │
│  │  - redeemPoints()                                     │   │
│  │  - getBalance()                                       │   │
│  │  - getTransactions()                                  │   │
│  └──────────────────────────────────────────────────────┘   │
└───────────┬─────────────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────────────┐
│                   DOMAIN LAYER                               │
│  ┌─────────────────┐  ┌──────────────────┐                 │
│  │ PointsBalance   │  │ PointsTransaction │                 │
│  │ (Model)         │  │ (Model)           │                 │
│  └─────────────────┘  └──────────────────┘                 │
└───────────┬─────────────────────────────────────────────────┘
            ▼
┌─────────────────────────────────────────────────────────────┐
│                   DATA LAYER                                 │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  Database Tables                                     │    │
│  │  - elsherif_points_balance                          │    │
│  │  - elsherif_points_transaction                      │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

#### 2️⃣ Multi-Frontend Support Strategy

**✅ ما نفعله (Best Practices):**
- **Service Contracts** - كل Business Logic في APIs
- **REST + GraphQL** - دعم كامل للاثنين
- **Decoupled Frontend** - Frontend يعتمد فقط على APIs
- **View Models** - Block Classes ترجع data فقط (no HTML logic)
- **Knockout JS Components** - للـ Default Theme (Checkout)
- **Separate GraphQL Resolvers** - للـ PWA

**❌ ما نتجنبه:**
- عدم خلط HTML في Business Logic
- عدم الاعتماد على Default Theme في Core Logic
- عدم Hardcode URLs أو Paths

---

## 🧠 اللوجيك والمنطق

### Flow 1: كسب النقاط عند الطلب

```
[العميل يكمل الطلب]
         ↓
[Event: sales_order_place_after]
         ↓
[Observer: EarnPointsOnOrderObserver]
         ↓
    ┌─────────────────────────────────┐
    │ 1. Check if order is eligible   │
    │    - Status = complete/processing│
    │    - Not a refund                │
    │    - Customer logged in          │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 2. Calculate Points              │
    │    points = grandTotal / rate   │
    │    (500 جنيه / 10 = 50 نقطة)    │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 3. Call PointsManagement Service │
    │    addPoints(customerId, points) │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 4. Update Database               │
    │    - Update balance table        │
    │    - Insert transaction record   │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 5. Send Email (optional)         │
    │    "You earned 50 points!"       │
    └─────────────────────────────────┘
```

**الكود:**
```php
// Observer/EarnPointsOnOrderObserver.php
public function execute(Observer $observer)
{
    $order = $observer->getEvent()->getOrder();
    
    // 1. Validation
    if (!$this->isEligible($order)) {
        return;
    }
    
    // 2. Calculate
    $points = $this->calculator->calculateEarnedPoints($order);
    
    // 3. Add Points
    $this->pointsManagement->addPoints(
        $order->getCustomerId(),
        $points,
        'order_complete',
        $order->getId(),
        $this->getExpirationDate()
    );
}
```

---

### Flow 2: استبدال النقاط في الـ Checkout

```
[العميل في صفحة Checkout]
         ↓
[يدخل عدد النقاط للاستخدام]
         ↓
[AJAX Call: POST /V1/loyalty/redeem]
         ↓
    ┌─────────────────────────────────┐
    │ 1. Validate Request              │
    │    - Customer has enough points  │
    │    - Points > 0                  │
    │    - Quote exists                │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 2. Calculate Discount            │
    │    discount = points / rate      │
    │    (100 نقطة / 10 = 10 جنيه)     │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 3. Apply to Quote                │
    │    Plugin: QuoteTotalPlugin      │
    │    - Add discount to totals      │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 4. Save Quote Extension Attr     │
    │    quote->loyaltyPointsUsed=100  │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 5. Return Updated Totals         │
    │    frontend updates UI           │
    └─────────────────────────────────┘
         ↓
[العميل يكمل الطلب]
         ↓
[Observer: DeductPointsOnOrderObserver]
         ↓
    ┌─────────────────────────────────┐
    │ 6. Deduct Points from Balance    │
    │    - Insert transaction (-100)   │
    │    - Update balance              │
    └─────────────────────────────────┘
```

**الكود:**
```php
// Plugin/Quote/Model/QuoteTotalPlugin.php
public function afterCollectTotals(
    \Magento\Quote\Model\Quote $subject,
    $result
) {
    $pointsUsed = $subject->getLoyaltyPointsUsed();
    
    if ($pointsUsed > 0) {
        $discount = $this->calculator->calculateDiscount($pointsUsed);
        
        // Apply discount
        $subject->setSubtotal($subject->getSubtotal() - $discount);
        $subject->setGrandTotal($subject->getGrandTotal() - $discount);
        $subject->setLoyaltyDiscount($discount);
    }
    
    return $result;
}
```

---

### Flow 3: انتهاء صلاحية النقاط (Cron)

```
[Cron: يعمل يومياً الساعة 2 صباحاً]
         ↓
[Cron/ExpirePoints.php::execute()]
         ↓
    ┌─────────────────────────────────┐
    │ 1. Find Expired Transactions     │
    │    WHERE expires_at < NOW()      │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 2. Loop Through Each Transaction │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 3. Deduct Points                 │
    │    - Insert transaction (expired)│
    │    - Update balance              │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 4. Send Email (optional)         │
    │    "50 points expired"           │
    └──────────────┬──────────────────┘
                   ↓
    ┌─────────────────────────────────┐
    │ 5. Log Results                   │
    │    "Expired 500 points for       │
    │     50 customers"                │
    └─────────────────────────────────┘
```

---

## 📁 هيكل الموديول

```
src/app/code/Elsherif/LoyaltySystem/
│
├── registration.php                      # Module registration
├── composer.json                         # Composer dependencies
├── README.md                             # هذا الملف
│
├── etc/
│   ├── module.xml                        # Module declaration
│   ├── db_schema.xml                     # Database schema
│   ├── di.xml                            # Dependency Injection
│   ├── config.xml                        # Default configuration
│   ├── events.xml                        # Event observers
│   ├── webapi.xml                        # REST API routes
│   ├── crontab.xml                       # Cron jobs
│   │
│   ├── adminhtml/
│   │   ├── routes.xml                    # Admin routes
│   │   ├── menu.xml                      # Admin menu
│   │   └── system.xml                    # Admin configuration
│   │
│   ├── frontend/
│   │   ├── routes.xml                    # Frontend routes
│   │   └── sections.xml                  # Customer data sections
│   │
│   └── graphql/
│       └── schema.graphqls               # GraphQL schema
│
├── Api/                                  # Service Contracts (Interfaces)
│   ├── Data/
│   │   ├── PointsBalanceInterface.php
│   │   ├── PointsTransactionInterface.php
│   │   └── RedemptionResultInterface.php
│   │
│   └── PointsManagementInterface.php     # Main service interface
│
├── Model/                                # Business Logic
│   ├── PointsBalance.php                 # Balance model
│   ├── PointsTransaction.php             # Transaction model
│   ├── PointsManagement.php              # Service implementation
│   ├── PointsCalculator.php              # Calculation logic
│   │
│   ├── ResourceModel/
│   │   ├── PointsBalance.php             # DB operations (balance)
│   │   ├── PointsTransaction.php         # DB operations (transaction)
│   │   │
│   │   └── PointsBalance/
│   │       └── Collection.php
│   │   └── PointsTransaction/
│   │       └── Collection.php
│   │
│   ├── Resolver/                         # GraphQL Resolvers
│   │   ├── CustomerPoints.php            # Query: customerPoints
│   │   ├── PointsTransactions.php        # Query: pointsTransactions
│   │   └── RedeemPoints.php              # Mutation: redeemPoints
│   │
│   └── Config.php                        # Configuration helper
│
├── Observer/                             # Event observers
│   ├── EarnPointsOnOrderObserver.php     # Earn points
│   ├── DeductPointsOnOrderObserver.php   # Redeem points
│   └── RefundPointsObserver.php          # Refund handling
│
├── Plugin/
│   ├── Quote/
│   │   └── Model/
│   │       └── QuoteTotalPlugin.php      # Apply discount to quote
│   │
│   └── Sales/
│       └── Model/
│           └── OrderPlugin.php           # Transfer quote data to order
│
├── Controller/
│   ├── Adminhtml/
│   │   └── Points/
│   │       ├── Index.php                 # Admin grid
│   │       ├── MassAdjust.php            # Bulk adjust
│   │       └── Export.php                # Export report
│   │
│   └── Customer/
│       ├── Index.php                     # Customer points page
│       └── Transactions.php              # Transaction history
│
├── Block/
│   ├── Adminhtml/
│   │   └── Dashboard/
│   │       └── PointsWidget.php          # Dashboard widget
│   │
│   └── Customer/
│       ├── Points.php                    # Points balance block
│       └── Transactions.php              # Transactions block
│
├── Ui/
│   └── Component/
│       └── Listing/
│           └── Column/
│               ├── Points.php            # Format points column
│               └── TransactionType.php   # Format type column
│
├── Cron/
│   └── ExpirePoints.php                  # Points expiration job
│
├── Console/
│   └── Command/
│       ├── AdjustPointsCommand.php       # CLI: adjust points
│       └── ExpirePointsCommand.php       # CLI: manual expiration
│
├── Helper/
│   └── Data.php                          # Helper functions
│
├── Setup/
│   └── Patch/
│       └── Data/
│           └── AddCustomerPointsAttribute.php  # EAV attribute
│
└── view/
    ├── adminhtml/
    │   ├── layout/
    │   │   ├── loyalty_points_index.xml
    │   │   └── customer_index_edit.xml   # Customer edit form
    │   │
    │   ├── ui_component/
    │   │   ├── loyalty_points_listing.xml
    │   │   └── loyalty_transaction_listing.xml
    │   │
    │   ├── templates/
    │   │   └── dashboard/
    │   │       └── points_widget.phtml
    │   │
    │   └── web/
    │       ├── css/
    │       │   └── loyalty.css
    │       └── js/
    │           └── points-chart.js       # Chart.js integration
    │
    └── frontend/
        ├── layout/
        │   ├── customer_account.xml      # Add menu item
        │   ├── loyalty_customer_index.xml
        │   ├── loyalty_customer_transactions.xml
        │   └── checkout_index_index.xml  # Checkout integration
        │
        ├── templates/
        │   ├── customer/
        │   │   ├── points.phtml          # Points balance page
        │   │   └── transactions.phtml    # Transactions page
        │   │
        │   └── checkout/
        │       └── points-form.phtml     # Redeem form (Default Theme)
        │
        ├── web/
        │   ├── css/
        │   │   └── loyalty.css
        │   │
        │   ├── js/
        │   │   └── view/
        │   │       └── checkout/
        │   │           └── points-discount.js  # Knockout component
        │   │
        │   └── template/
        │       └── checkout/
        │           └── points-form.html  # Knockout template
        │
        └── requirejs-config.js           # RequireJS config
```

---

## 💾 Database Schema

### Table 1: `elsherif_points_balance`
**الغرض:** تخزين رصيد النقاط الحالي لكل عميل

```sql
CREATE TABLE `elsherif_points_balance` (
    `balance_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL UNIQUE,
    `points` INT NOT NULL DEFAULT 0,
    `lifetime_points` INT NOT NULL DEFAULT 0,  -- Total points ever earned
    `points_spent` INT NOT NULL DEFAULT 0,      -- Total points spent
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `IDX_CUSTOMER` (`customer_id`),
    
    CONSTRAINT `FK_LOYALTY_BALANCE_CUSTOMER`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer_entity` (`entity_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Loyalty Points Balance';
```

**مثال بيانات:**
| balance_id | customer_id | points | lifetime_points | points_spent | updated_at |
|------------|-------------|--------|-----------------|--------------|------------|
| 1 | 5 | 350 | 500 | 150 | 2025-01-15 |
| 2 | 12 | 120 | 120 | 0 | 2025-01-14 |

---

### Table 2: `elsherif_points_transaction`
**الغرض:** سجل كامل لكل حركة نقاط (كسب/استخدام/انتهاء)

```sql
CREATE TABLE `elsherif_points_transaction` (
    `transaction_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `points` INT NOT NULL,                      -- +ve = earned, -ve = spent/expired
    `balance_after` INT NOT NULL,               -- Balance snapshot after transaction
    `action` VARCHAR(50) NOT NULL,              -- order_complete, redemption, expired, admin_adjust, refund
    `reference_id` INT UNSIGNED NULL,           -- order_id, adjustment_id, etc.
    `reference_type` VARCHAR(50) NULL,          -- order, adjustment, manual
    `expires_at` DATETIME NULL,                 -- When these points expire
    `comment` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `IDX_CUSTOMER` (`customer_id`),
    INDEX `IDX_ACTION` (`action`),
    INDEX `IDX_EXPIRES` (`expires_at`),
    INDEX `IDX_REFERENCE` (`reference_type`, `reference_id`),
    
    CONSTRAINT `FK_LOYALTY_TRANSACTION_CUSTOMER`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer_entity` (`entity_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Loyalty Points Transactions';
```

**مثال بيانات:**
| transaction_id | customer_id | points | balance_after | action | reference_id | expires_at | created_at |
|----------------|-------------|--------|---------------|--------|--------------|------------|------------|
| 1 | 5 | +50 | 50 | order_complete | 100245 | 2025-07-15 | 2025-01-15 |
| 2 | 5 | -30 | 20 | redemption | 100250 | NULL | 2025-01-16 |
| 3 | 5 | -20 | 0 | expired | 1 | NULL | 2025-07-15 |

---

### Table 3: `quote` Extension (via Extension Attributes)
**الحقول المضافة:**
- `loyalty_points_used` (INT) - عدد النقاط المستخدمة
- `loyalty_discount_amount` (DECIMAL) - قيمة الخصم بالمبلغ

يتم إضافتها عبر `extension_attributes.xml`:

```xml
<config>
    <extension_attributes for="Magento\Quote\Api\Data\CartInterface">
        <attribute code="loyalty_points_used" type="int"/>
        <attribute code="loyalty_discount_amount" type="float"/>
    </extension_attributes>
</config>
```

---

### Table 4: `sales_order` Extension (via Extension Attributes)
**الحقول المضافة:**
- `loyalty_points_earned` (INT)
- `loyalty_points_used` (INT)
- `loyalty_discount_amount` (DECIMAL)

---

## 🔌 API Documentation

### REST API Endpoints

#### 1. Get Customer Points Balance
```http
GET /rest/V1/loyalty/balance/:customerId
```

**Response:**
```json
{
    "balance_id": 1,
    "customer_id": 5,
    "points": 350,
    "lifetime_points": 500,
    "points_spent": 150,
    "updated_at": "2025-01-15 10:30:00"
}
```

---

#### 2. Add Points (Admin only)
```http
POST /rest/V1/loyalty/points/add
```

**Request:**
```json
{
    "customerId": 5,
    "points": 100,
    "action": "admin_adjust",
    "comment": "Bonus points for customer satisfaction",
    "expiresAt": "2025-12-31"
}
```

**Response:**
```json
{
    "success": true,
    "new_balance": 450,
    "transaction_id": 123
}
```

---

#### 3. Redeem Points (Apply to Quote)
```http
POST /rest/V1/loyalty/redeem
```

**Request:**
```json
{
    "quoteId": 456,
    "points": 100
}
```

**Response:**
```json
{
    "success": true,
    "points_used": 100,
    "discount_amount": 10.00,
    "new_balance": 250,
    "totals": {
        "subtotal": 500.00,
        "discount": -10.00,
        "grand_total": 490.00
    }
}
```

---

#### 4. Get Transaction History
```http
GET /rest/V1/loyalty/transactions/:customerId?pageSize=20&currentPage=1
```

**Response:**
```json
{
    "items": [
        {
            "transaction_id": 1,
            "points": 50,
            "action": "order_complete",
            "reference_id": 100245,
            "expires_at": "2025-07-15",
            "created_at": "2025-01-15 14:30:00"
        },
        {
            "transaction_id": 2,
            "points": -30,
            "action": "redemption",
            "reference_id": 100250,
            "created_at": "2025-01-16 09:15:00"
        }
    ],
    "total_count": 2
}
```

---

#### 5. Cancel Redemption (Remove discount from quote)
```http
POST /rest/V1/loyalty/cancel
```

**Request:**
```json
{
    "quoteId": 456
}
```

---

## 🎨 GraphQL Schema

```graphql
type Query {
    """
    Get current customer's points balance
    Requires customer token
    """
    customerPoints: CustomerPointsBalance @resolver(class: "Elsherif\\LoyaltySystem\\Model\\Resolver\\CustomerPoints") @doc(description: "Get loyalty points balance") @cache(cacheIdentity: "Elsherif\\LoyaltySystem\\Model\\Resolver\\Identity\\PointsIdentity")
    
    """
    Get transaction history for current customer
    """
    pointsTransactions(
        pageSize: Int = 20 @doc(description: "How many items per page")
        currentPage: Int = 1 @doc(description: "Page number")
    ): PointsTransactionOutput @resolver(class: "Elsherif\\LoyaltySystem\\Model\\Resolver\\PointsTransactions")
}

type Mutation {
    """
    Apply points discount to cart
    """
    redeemPoints(
        input: RedeemPointsInput!
    ): RedeemPointsOutput @resolver(class: "Elsherif\\LoyaltySystem\\Model\\Resolver\\RedeemPoints")
    
    """
    Cancel points redemption
    """
    cancelPointsRedemption(
        cartId: String!
    ): CancelRedemptionOutput @resolver(class: "Elsherif\\LoyaltySystem\\Model\\Resolver\\CancelRedemption")
}

type CustomerPointsBalance {
    balance_id: Int
    customer_id: Int
    points: Int @doc(description: "Available points")
    lifetime_points: Int @doc(description: "Total points earned all time")
    points_spent: Int @doc(description: "Total points spent")
    updated_at: String
}

type PointsTransaction {
    transaction_id: Int
    points: Int @doc(description: "Positive = earned, Negative = spent/expired")
    balance_after: Int
    action: String @doc(description: "order_complete, redemption, expired, admin_adjust")
    reference_id: Int
    reference_type: String
    expires_at: String
    comment: String
    created_at: String
}

type PointsTransactionOutput {
    items: [PointsTransaction]
    total_count: Int
}

input RedeemPointsInput {
    cart_id: String!
    points: Int! @doc(description: "Number of points to redeem")
}

type RedeemPointsOutput {
    success: Boolean
    message: String
    points_used: Int
    discount_amount: Float
    new_balance: Int
    cart: Cart
}

type CancelRedemptionOutput {
    success: Boolean
    message: String
}

"""
Extend CartPrices to show loyalty discount
"""
type CartPrices {
    loyalty_discount: Money @doc(description: "Loyalty points discount")
}

"""
Extend Order to show points data
"""
type Order {
    loyalty_points_earned: Int
    loyalty_points_used: Int
    loyalty_discount_amount: Float
}
```

---

## 🎨 Frontend Integration

### A) Default Theme (Luma/Blank) Integration

#### 1️⃣ Customer Account - Points Balance Page

**File:** `view/frontend/templates/customer/points.phtml`

```php
<?php
/**
 * @var \Elsherif\LoyaltySystem\Block\Customer\Points $block
 */
$balance = $block->getPointsBalance();
?>

<div class="loyalty-points-page">
    <div class="page-title-wrapper">
        <h1 class="page-title">
            <span class="base"><?= __('My Loyalty Points') ?></span>
        </h1>
    </div>
    
    <div class="points-summary">
        <div class="points-card current-points">
            <h2><?= __('Available Points') ?></h2>
            <div class="points-value"><?= $block->escapeHtml($balance->getPoints()) ?></div>
            <p class="points-worth">
                <?= __('Worth: %1', $block->formatPrice($block->getPointsValue($balance->getPoints()))) ?>
            </p>
        </div>
        
        <div class="points-card lifetime-points">
            <h2><?= __('Lifetime Points') ?></h2>
            <div class="points-value"><?= $block->escapeHtml($balance->getLifetimePoints()) ?></div>
        </div>
        
        <div class="points-card spent-points">
            <h2><?= __('Points Spent') ?></h2>
            <div class="points-value"><?= $block->escapeHtml($balance->getPointsSpent()) ?></div>
        </div>
    </div>
    
    <div class="points-info">
        <h3><?= __('How it works?') ?></h3>
        <ul>
            <li><?= __('Earn %1 point for every %2 spent', 1, $block->formatPrice($block->getEarnRate())) ?></li>
            <li><?= __('Redeem %1 points for %2 discount', $block->getRedeemRate(), $block->formatPrice(1)) ?></li>
            <li><?= __('Points expire after %1 days', $block->getExpirationDays()) ?></li>
        </ul>
    </div>
    
    <div class="actions-toolbar">
        <div class="primary">
            <a href="<?= $block->escapeUrl($block->getTransactionsUrl()) ?>" class="action primary">
                <?= __('View Transaction History') ?>
            </a>
        </div>
    </div>
</div>
```

---

#### 2️⃣ Checkout Integration (Knockout JS)

**File:** `view/frontend/web/js/view/checkout/points-discount.js`

```javascript
/**
 * Loyalty Points Knockout Component for Checkout
 */
define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Ui/js/model/messageList'
], function (
    ko,
    $,
    Component,
    quote,
    totals,
    storage,
    urlBuilder,
    errorProcessor,
    messageList
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Elsherif_LoyaltySystem/checkout/points-form'
        },

        /**
         * Observables
         */
        pointsToUse: ko.observable(0),
        availablePoints: ko.observable(0),
        isLoading: ko.observable(false),
        isApplied: ko.observable(false),
        maxPointsAllowed: ko.observable(0),

        /**
         * Initialize
         */
        initialize: function () {
            this._super();
            this.loadCustomerPoints();
            
            // Watch quote changes
            quote.totals.subscribe(function (totals) {
                this.updateMaxPoints(totals);
            }, this);
        },

        /**
         * Load customer points via AJAX
         */
        loadCustomerPoints: function () {
            var self = this;
            var serviceUrl = urlBuilder.createUrl('/loyalty/balance/:customerId', {
                customerId: window.customerData.id
            });

            storage.get(serviceUrl)
                .done(function (response) {
                    self.availablePoints(response.points);
                    self.updateMaxPoints(quote.totals());
                })
                .fail(function (response) {
                    errorProcessor.process(response);
                });
        },

        /**
         * Update max points based on cart total
         */
        updateMaxPoints: function (totals) {
            // Max points = cart total / redemption rate
            var maxByCart = Math.floor(totals.grand_total / this.getRedemptionRate());
            var maxByBalance = this.availablePoints();
            
            this.maxPointsAllowed(Math.min(maxByCart, maxByBalance));
        },

        /**
         * Apply points discount
         */
        applyPoints: function () {
            var self = this;
            var points = parseInt(this.pointsToUse());

            // Validation
            if (!this.validatePoints(points)) {
                return;
            }

            this.isLoading(true);

            var payload = {
                quoteId: quote.getQuoteId(),
                points: points
            };

            storage.post(
                urlBuilder.createUrl('/loyalty/redeem', {}),
                JSON.stringify(payload)
            ).done(function (response) {
                if (response.success) {
                    self.isApplied(true);
                    
                    // Reload totals
                    var deferred = $.Deferred();
                    totals.isLoading(true);
                    
                    storage.get(
                        urlBuilder.createUrl('/carts/mine/totals', {})
                    ).done(function (data) {
                        quote.setTotals(data);
                        deferred.resolve();
                    }).always(function () {
                        totals.isLoading(false);
                    });

                    messageList.addSuccessMessage({
                        message: response.message
                    });
                }
            }).fail(function (response) {
                errorProcessor.process(response, messageList);
            }).always(function () {
                self.isLoading(false);
            });
        },

        /**
         * Cancel points redemption
         */
        cancelPoints: function () {
            var self = this;
            this.isLoading(true);

            storage.post(
                urlBuilder.createUrl('/loyalty/cancel', {}),
                JSON.stringify({ quoteId: quote.getQuoteId() })
            ).done(function () {
                self.isApplied(false);
                self.pointsToUse(0);
                
                // Reload totals
                var deferred = $.Deferred();
                totals.isLoading(true);
                
                storage.get(
                    urlBuilder.createUrl('/carts/mine/totals', {})
                ).done(function (data) {
                    quote.setTotals(data);
                    deferred.resolve();
                }).always(function () {
                    totals.isLoading(false);
                });
            }).always(function () {
                self.isLoading(false);
            });
        },

        /**
         * Validate points input
         */
        validatePoints: function (points) {
            if (!points || points <= 0) {
                messageList.addErrorMessage({
                    message: $.mage.__('Please enter a valid number of points.')
                });
                return false;
            }

            if (points > this.maxPointsAllowed()) {
                messageList.addErrorMessage({
                    message: $.mage.__('You can use maximum %1 points.').replace('%1', this.maxPointsAllowed())
                });
                return false;
            }

            return true;
        },

        /**
         * Get redemption rate from config
         */
        getRedemptionRate: function () {
            return window.checkoutConfig.loyaltyConfig.redemptionRate || 10;
        },

        /**
         * Calculate discount preview
         */
        getDiscountPreview: function () {
            var points = parseInt(this.pointsToUse()) || 0;
            return (points / this.getRedemptionRate()).toFixed(2);
        }
    });
});
```

**Knockout Template:** `view/frontend/web/template/checkout/points-form.html`

```html
<!-- ko if: availablePoints() > 0 -->
<div class="loyalty-points-block" data-bind="css: { 'applied': isApplied() }">
    <div class="block-title">
        <strong><?php echo __('Use Loyalty Points'); ?></strong>
    </div>
    
    <div class="block-content">
        <p class="available-points">
            <?php echo __('You have'); ?>
            <strong data-bind="text: availablePoints()"></strong>
            <?php echo __('points available'); ?>
        </p>
        
        <!-- ko ifnot: isApplied() -->
        <div class="field points-input">
            <label class="label" for="points-to-use">
                <span><?php echo __('Points to use'); ?></span>
            </label>
            <div class="control">
                <input type="number"
                       id="points-to-use"
                       name="points_to_use"
                       class="input-text"
                       data-bind="value: pointsToUse, attr: { max: maxPointsAllowed() }"
                       min="0" />
                <span class="note" data-bind="visible: pointsToUse() > 0">
                    <?php echo __('Discount:'); ?>
                    <strong data-bind="text: getDiscountPreview()"></strong>
                    <?php echo __('EGP'); ?>
                </span>
            </div>
        </div>
        
        <div class="actions-toolbar">
            <button type="button"
                    class="action action-apply"
                    data-bind="click: applyPoints, enable: !isLoading()">
                <span data-bind="i18n: 'Apply Points'"></span>
            </button>
        </div>
        <!-- /ko -->
        
        <!-- ko if: isApplied() -->
        <div class="applied-message">
            <span class="icon success"></span>
            <span data-bind="text: pointsToUse()"></span>
            <?php echo __('points applied'); ?>
            
            <button type="button"
                    class="action action-cancel"
                    data-bind="click: cancelPoints">
                <span data-bind="i18n: 'Cancel'"></span>
            </button>
        </div>
        <!-- /ko -->
        
        <!-- ko if: isLoading() -->
        <div class="loader">
            <img src="<?php echo $block->getViewFileUrl('images/loader-1.gif'); ?>" alt="Loading...">
        </div>
        <!-- /ko -->
    </div>
</div>
<!-- /ko -->
```

---

### B) PWA Studio Integration

#### 1️⃣ GraphQL Talons (Custom Hook)

**File:** `talons/useLoyaltyPoints.js` (في PWA Studio)

```javascript
import { useQuery, useMutation } from '@apollo/client';
import { useCartContext } from '@magento/peregrine/lib/context/cart';

import GET_CUSTOMER_POINTS from './queries/getCustomerPoints.graphql';
import REDEEM_POINTS from './mutations/redeemPoints.graphql';
import CANCEL_REDEMPTION from './mutations/cancelRedemption.graphql';

export const useLoyaltyPoints = () => {
    const [{ cartId }] = useCartContext();

    // Query: Get customer points
    const {
        data: pointsData,
        loading: loadingPoints,
        error: pointsError
    } = useQuery(GET_CUSTOMER_POINTS, {
        fetchPolicy: 'cache-and-network'
    });

    // Mutation: Redeem points
    const [
        redeemPoints,
        { loading: redeeming, error: redeemError }
    ] = useMutation(REDEEM_POINTS, {
        refetchQueries: ['GetCartDetails', 'GetCustomerPoints']
    });

    // Mutation: Cancel redemption
    const [
        cancelRedemption,
        { loading: cancelling }
    ] = useMutation(CANCEL_REDEMPTION, {
        refetchQueries: ['GetCartDetails', 'GetCustomerPoints']
    });

    const handleRedeemPoints = async (points) => {
        try {
            const { data } = await redeemPoints({
                variables: {
                    input: {
                        cart_id: cartId,
                        points: parseInt(points)
                    }
                }
            });

            return data.redeemPoints;
        } catch (error) {
            console.error('Error redeeming points:', error);
            throw error;
        }
    };

    const handleCancelRedemption = async () => {
        try {
            await cancelRedemption({
                variables: {
                    cartId
                }
            });
        } catch (error) {
            console.error('Error cancelling redemption:', error);
            throw error;
        }
    };

    return {
        pointsBalance: pointsData?.customerPoints || null,
        loadingPoints,
        pointsError,
        redeemPoints: handleRedeemPoints,
        cancelRedemption: handleCancelRedemption,
        isProcessing: redeeming || cancelling,
        redeemError
    };
};
```

**GraphQL Query:** `queries/getCustomerPoints.graphql`

```graphql
query GetCustomerPoints {
    customerPoints {
        balance_id
        customer_id
        points
        lifetime_points
        points_spent
        updated_at
    }
}
```

**GraphQL Mutation:** `mutations/redeemPoints.graphql`

```graphql
mutation RedeemPoints($input: RedeemPointsInput!) {
    redeemPoints(input: $input) {
        success
        message
        points_used
        discount_amount
        new_balance
        cart {
            id
            prices {
                grand_total {
                    value
                    currency
                }
                loyalty_discount {
                    value
                    currency
                }
            }
        }
    }
}
```

---

#### 2️⃣ React Component

**File:** `components/LoyaltyPoints/loyaltyPoints.js` (PWA Studio)

```jsx
import React, { useState } from 'react';
import { useStyle } from '@magento/venia-ui/lib/classify';
import { FormattedMessage, useIntl } from 'react-intl';
import { useLoyaltyPoints } from '../../talons/useLoyaltyPoints';
import defaultClasses from './loyaltyPoints.module.css';
import Button from '@magento/venia-ui/lib/components/Button';
import TextInput from '@magento/venia-ui/lib/components/TextInput';
import { Message } from '@magento/venia-ui/lib/components/Field';

const LoyaltyPoints = (props) => {
    const classes = useStyle(defaultClasses, props.classes);
    const { formatMessage } = useIntl();
    
    const {
        pointsBalance,
        loadingPoints,
        redeemPoints,
        cancelRedemption,
        isProcessing
    } = useLoyaltyPoints();

    const [pointsToUse, setPointsToUse] = useState('');
    const [isApplied, setIsApplied] = useState(false);
    const [error, setError] = useState(null);

    if (loadingPoints) {
        return <div className={classes.loader}>Loading points...</div>;
    }

    if (!pointsBalance || pointsBalance.points === 0) {
        return null;
    }

    const handleApply = async () => {
        try {
            setError(null);
            const result = await redeemPoints(pointsToUse);
            
            if (result.success) {
                setIsApplied(true);
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const handleCancel = async () => {
        try {
            await cancelRedemption();
            setIsApplied(false);
            setPointsToUse('');
        } catch (err) {
            setError(err.message);
        }
    };

    const discountPreview = (pointsToUse / 10).toFixed(2); // Assuming rate = 10

    return (
        <div className={classes.root}>
            <div className={classes.title}>
                <FormattedMessage
                    id="loyaltyPoints.title"
                    defaultMessage="Use Loyalty Points"
                />
            </div>

            <div className={classes.availablePoints}>
                <FormattedMessage
                    id="loyaltyPoints.available"
                    defaultMessage="You have {points} points available"
                    values={{ points: <strong>{pointsBalance.points}</strong> }}
                />
            </div>

            {!isApplied ? (
                <>
                    <TextInput
                        field="pointsToUse"
                        type="number"
                        label={formatMessage({
                            id: 'loyaltyPoints.inputLabel',
                            defaultMessage: 'Points to use'
                        })}
                        value={pointsToUse}
                        onChange={(e) => setPointsToUse(e.target.value)}
                        min="0"
                        max={pointsBalance.points}
                    />

                    {pointsToUse > 0 && (
                        <div className={classes.preview}>
                            <FormattedMessage
                                id="loyaltyPoints.discount"
                                defaultMessage="Discount: {amount} EGP"
                                values={{ amount: discountPreview }}
                            />
                        </div>
                    )}

                    <Button
                        priority="high"
                        onClick={handleApply}
                        disabled={!pointsToUse || isProcessing}
                    >
                        <FormattedMessage
                            id="loyaltyPoints.apply"
                            defaultMessage="Apply Points"
                        />
                    </Button>
                </>
            ) : (
                <div className={classes.applied}>
                    <span className={classes.successIcon}>✓</span>
                    <FormattedMessage
                        id="loyaltyPoints.appliedMessage"
                        defaultMessage="{points} points applied"
                        values={{ points: pointsToUse }}
                    />
                    <Button priority="low" onClick={handleCancel}>
                        <FormattedMessage
                            id="loyaltyPoints.cancel"
                            defaultMessage="Cancel"
                        />
                    </Button>
                </div>
            )}

            {error && <Message fieldState={{ error }}>{error}</Message>}
        </div>
    );
};

export default LoyaltyPoints;
```

---

## ⚙️ Configuration

### Admin Configuration Path:
**Stores → Configuration → Elsherif Extensions → Loyalty System**

### Available Settings:

```xml
<!-- etc/adminhtml/system.xml -->
<config>
    <system>
        <section id="loyalty_system" translate="label" sortOrder="200" showInDefault="1" showInWebsite="1" showInStore="1">
            <label>Loyalty System</label>
            <tab>elsherif</tab>
            <resource>Elsherif_LoyaltySystem::config</resource>
            
            <group id="general" translate="label" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                <label>General Settings</label>
                
                <field id="enabled" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Enable Loyalty System</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                
                <field id="earn_rate" translate="label comment" type="text" sortOrder="20" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Points Earning Rate</label>
                    <comment>Customer earns 1 point for every X amount spent</comment>
                    <validate>required-entry validate-number validate-greater-than-zero</validate>
                </field>
                
                <field id="redeem_rate" translate="label comment" type="text" sortOrder="30" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Points Redemption Rate</label>
                    <comment>X points = 1 currency unit discount</comment>
                    <validate>required-entry validate-number validate-greater-than-zero</validate>
                </field>
                
                <field id="expiration_days" translate="label comment" type="text" sortOrder="40" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Points Expiration (Days)</label>
                    <comment>Points will expire after X days. Set to 0 for no expiration.</comment>
                    <validate>validate-number validate-zero-or-greater</validate>
                </field>
                
                <field id="min_points_redeem" translate="label" type="text" sortOrder="50" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Minimum Points to Redeem</label>
                    <validate>validate-number validate-zero-or-greater</validate>
                </field>
                
                <field id="max_points_per_order" translate="label comment" type="text" sortOrder="60" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Maximum Points Per Order</label>
                    <comment>Maximum points that can be redeemed in a single order. Leave empty for no limit.</comment>
                    <validate>validate-number</validate>
                </field>
            </group>
            
            <group id="earning_rules" translate="label" sortOrder="20" showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Earning Rules</label>
                
                <field id="earn_on_tax" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Earn Points on Tax</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                
                <field id="earn_on_shipping" translate="label" type="select" sortOrder="20" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Earn Points on Shipping</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
            </group>
            
            <group id="email" translate="label" sortOrder="30" showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Email Notifications</label>
                
                <field id="send_earn_email" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Send Email on Points Earned</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                
                <field id="send_expiry_reminder" translate="label" type="select" sortOrder="20" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Send Expiry Reminder Email</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
                
                <field id="expiry_reminder_days" translate="label comment" type="text" sortOrder="30" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Reminder Days Before Expiry</label>
                    <comment>Send reminder X days before points expire</comment>
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

## 📦 Installation

### Step 1: Copy Module Files
```bash
cp -r Elsherif/LoyaltySystem /path/to/magento/app/code/Elsherif/
```

### Step 2: Enable Module
```bash
php bin/magento module:enable Elsherif_LoyaltySystem
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

### Step 3: Configure
1. Login to Admin Panel
2. Go to **Stores → Configuration → Elsherif Extensions → Loyalty System**
3. Enable the module
4. Set earning/redemption rates
5. Save Config

### Step 4: Setup Cron
Ensure Magento cron is running:
```bash
* * * * * php /path/to/magento/bin/magento cron:run
```

---

## 🧪 Testing

### Unit Tests Example

**File:** `Test/Unit/Model/PointsCalculatorTest.php`

```php
<?php
namespace Elsherif\LoyaltySystem\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Elsherif\LoyaltySystem\Model\PointsCalculator;
use Elsherif\LoyaltySystem\Model\Config;

class PointsCalculatorTest extends TestCase
{
    private $calculator;
    private $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->calculator = new PointsCalculator($this->configMock);
    }

    public function testCalculateEarnedPoints()
    {
        // Arrange
        $orderTotal = 500.00;
        $earnRate = 10; // 1 point per 10 currency
        
        $this->configMock->method('getEarnRate')->willReturn($earnRate);
        
        // Act
        $points = $this->calculator->calculateEarnedPoints($orderTotal);
        
        // Assert
        $this->assertEquals(50, $points);
    }

    public function testCalculateDiscount()
    {
        // Arrange
        $pointsToRedeem = 100;
        $redeemRate = 10; // 10 points = 1 currency
        
        $this->configMock->method('getRedeemRate')->willReturn($redeemRate);
        
        // Act
        $discount = $this->calculator->calculateDiscount($pointsToRedeem);
        
        // Assert
        $this->assertEquals(10.00, $discount);
    }
}
```

### Integration Tests

```bash
php bin/magento dev:tests:run integration -- --filter=Elsherif_LoyaltySystem
```

---

## 🔧 Troubleshooting

### Issue 1: Points not being added after order

**Solution:**
1. Check if module is enabled in config
2. Verify observer is registered in `events.xml`
3. Check order status (must be `complete` or `processing`)
4. Check logs: `var/log/loyalty.log`

```bash
tail -f var/log/loyalty.log
```

---

### Issue 2: GraphQL not returning points

**Solution:**
1. Ensure customer is logged in (requires customer token)
2. Check GraphQL schema:
```bash
php bin/magento cache:clean config
```

3. Test query in GraphQL playground:
```graphql
{
  customerPoints {
    points
  }
}
```

---

### Issue 3: Checkout discount not applying

**Solution:**
1. Clear quote cache
2. Check plugin priority in `di.xml`
3. Verify quote extension attributes are saved

```bash
php bin/magento cache:clean quote
```

---

## 📚 Key Files Reference

| File | Purpose |
|------|---------|
| `Api/PointsManagementInterface.php` | Service contract |
| `Model/PointsManagement.php` | Main business logic |
| `Observer/EarnPointsOnOrderObserver.php` | Earn points on order |
| `Plugin/Quote/Model/QuoteTotalPlugin.php` | Apply discount to checkout |
| `Model/Resolver/CustomerPoints.php` | GraphQL resolver |
| `view/frontend/web/js/view/checkout/points-discount.js` | Knockout component |
| `Cron/ExpirePoints.php` | Points expiration cron |

---

## 🎓 Learning Outcomes

بعد إكمال هذا الموديول، ستكون تعلمت:

✅ **Service Contracts** - كيفية بناء APIs قابلة للتوسع  
✅ **REST + GraphQL** - التكامل مع Frontends مختلفة  
✅ **Observers** - التفاعل مع أحداث Magento  
✅ **Plugins** - تعديل سلوك Classes دون تغييرها  
✅ **Knockout JS** - بناء UI Components للـ Checkout  
✅ **Extension Attributes** - إضافة بيانات للـ Entities الموجودة  
✅ **Cron Jobs** - تنفيذ مهام مجدولة  
✅ **Multi-Frontend Architecture** - دعم Default + PWA  

---

## 🤝 Contributing

لو عندك اقتراحات أو تحسينات:
1. Fork the repository
2. Create feature branch
3. Submit pull request

---

## 📞 Support

للمساعدة أو الاستفسارات:
- Email: support@example.com
- GitHub Issues: [Link]

---

## 📄 License

MIT License

---

**Made with ❤️ for Magento Developers**

---

## 🚀 Next Steps

بعد إكمال `LoyaltySystem`، انتقل إلى:
1. **LoyaltyCoupon** - دمج الكوبونات مع النقاط
2. **LoyaltyAdmin** - لوحة تحكم Admin كاملة
3. **PWA Integration** - تطبيق PWA Studio كامل

---

**Happy Coding! 🎉**
