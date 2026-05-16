# Elsherif Loyalty System - API Documentation

## Overview

The Elsherif Loyalty System provides comprehensive REST and GraphQL APIs for managing customer loyalty points in Magento 2. This documentation covers all available endpoints, authentication requirements, and usage examples.

---

## Table of Contents

1. [Authentication](#authentication)
2. [REST API](#rest-api)
   - [Get Customer Balance](#get-customer-balance)
   - [Add Points](#add-points-admin)
   - [Redeem Points](#redeem-points)
   - [Cancel Redemption](#cancel-redemption)
3. [GraphQL API](#graphql-api)
   - [Queries](#queries)
   - [Mutations](#mutations)
   - [Extended Types](#extended-types)
4. [Error Handling](#error-handling)
5. [Rate Limiting](#rate-limiting)
6. [Webhooks](#webhooks)

---

## Authentication

### REST API
All REST API endpoints require authentication via Bearer token.

```bash
curl -X GET "https://your-store.com/rest/V1/loyalty/balance/123" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### GraphQL API
GraphQL requires customer authentication token in the header.

```bash
curl -X POST "https://your-store.com/graphql" \
  -H "Authorization: Bearer CUSTOMER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query": "{ customerLoyaltyPoints { points } }"}'
```

---

## REST API

### Base URL
```
https://your-store.com/rest/V1/loyalty
```

---

### Get Customer Balance

Retrieve the current loyalty points balance for a customer.

**Endpoint:** `GET /V1/loyalty/balance/:customerId`

**Authentication:** Customer Token (self) or Admin Token

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| customerId | int | Yes | Customer ID |

**Response:**
```json
{
  "balance_id": 1,
  "customer_id": 123,
  "points": 500,
  "lifetime_points": 1500,
  "points_spent": 1000,
  "updated_at": "2024-01-15 10:30:00"
}
```

**Example:**
```bash
curl -X GET "https://your-store.com/rest/V1/loyalty/balance/123" \
  -H "Authorization: Bearer CUSTOMER_TOKEN"
```

---

### Add Points (Admin)

Add loyalty points to a customer's account. Admin only.

**Endpoint:** `POST /V1/loyalty/points/add`

**Authentication:** Admin Token with `Elsherif_LoyaltySystem::points_manage` ACL

**Request Body:**
```json
{
  "customerId": 123,
  "points": 100,
  "action": "admin",
  "referenceId": null,
  "expiresAt": "2024-12-31 23:59:59",
  "comment": "Bonus points for VIP customer"
}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| customerId | int | Yes | Target customer ID |
| points | int | Yes | Points to add |
| action | string | Yes | Action type: `admin`, `earn`, `refund` |
| referenceId | int | No | Related order ID |
| expiresAt | string | No | Expiration datetime |
| comment | string | No | Admin comment |

**Response:**
```json
{
  "success": true
}
```

**Example:**
```bash
curl -X POST "https://your-store.com/rest/V1/loyalty/points/add" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customerId": 123,
    "points": 100,
    "action": "admin",
    "comment": "Welcome bonus"
  }'
```

---

### Redeem Points

Apply loyalty points as discount to a quote/cart.

**Endpoint:** `POST /V1/loyalty/redeem`

**Authentication:** Customer Token

**Request Body:**
```json
{
  "quoteId": 456,
  "points": 100
}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| quoteId | int | Yes | Cart/Quote ID |
| points | int | Yes | Points to redeem |

**Response:**
```json
{
  "success": true,
  "message": "Points applied successfully.",
  "points_used": 100,
  "discount_amount": 10.00,
  "new_balance": 400
}
```

**Example:**
```bash
curl -X POST "https://your-store.com/rest/V1/loyalty/redeem" \
  -H "Authorization: Bearer CUSTOMER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "quoteId": 456,
    "points": 100
  }'
```

---

### Cancel Redemption

Remove loyalty points discount from a cart.

**Endpoint:** `POST /V1/loyalty/cancel`

**Authentication:** Customer Token

**Request Body:**
```json
{
  "quoteId": 456
}
```

**Response:**
```json
{
  "success": true
}
```

---

## GraphQL API

### Endpoint
```
POST https://your-store.com/graphql
```

---

## Queries

### customerLoyaltyPoints

Get the current customer's loyalty points data.

```graphql
query {
  customerLoyaltyPoints {
    balance_id
    customer_id
    points
    lifetime_points
    points_spent
    points_pending
    tier {
      name
      code
      min_points
      benefits
    }
    next_expiry {
      points
      expiry_date
    }
    updated_at
  }
}
```

**Response:**
```json
{
  "data": {
    "customerLoyaltyPoints": {
      "balance_id": 1,
      "customer_id": 123,
      "points": 500,
      "lifetime_points": 1500,
      "points_spent": 1000,
      "points_pending": 50,
      "tier": {
        "name": "Silver",
        "code": "silver",
        "min_points": 1000,
        "benefits": ["10% bonus points"]
      },
      "next_expiry": {
        "points": 100,
        "expiry_date": "2024-06-30 23:59:59"
      },
      "updated_at": "2024-01-15 10:30:00"
    }
  }
}
```

---

### customerPointsHistory

Get customer's points transaction history with pagination.

```graphql
query {
  customerPointsHistory(pageSize: 10, currentPage: 1) {
    items {
      transaction_id
      action
      points
      balance_after
      reference_id
      comment
      created_at
      expires_at
    }
    page_info {
      page_size
      current_page
      total_pages
    }
    total_count
  }
}
```

---

### loyaltyConfig

Get loyalty program configuration (public).

```graphql
query {
  loyaltyConfig {
    is_enabled
    earn_rate
    redeem_rate
    min_points_to_redeem
    max_points_per_order
    points_expiry_days
    allow_partial_redemption
  }
}
```

---

## Mutations

### applyLoyaltyPointsToCart

Apply loyalty points discount to cart.

```graphql
mutation ApplyPoints($input: ApplyLoyaltyPointsInput!) {
  applyLoyaltyPointsToCart(input: $input) {
    success
    message
    points_used
    discount_amount
    remaining_balance
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

**Variables:**
```json
{
  "input": {
    "cart_id": "MASKED_CART_ID",
    "points": 100
  }
}
```

---

### removeLoyaltyPointsFromCart

Remove loyalty points discount from cart.

```graphql
mutation RemovePoints($cartId: String!) {
  removeLoyaltyPointsFromCart(cart_id: $cartId) {
    success
    message
    cart {
      id
      prices {
        grand_total {
          value
          currency
        }
      }
    }
  }
}
```

---

## Extended Types

### ProductInterface Extension

All product types include loyalty points information.

```graphql
query GetProduct($sku: String!) {
  products(filter: { sku: { eq: $sku } }) {
    items {
      sku
      name
      price_range {
        minimum_price {
          final_price {
            value
          }
        }
      }
      loyalty_points_earn  # Points earned when purchasing
    }
  }
}
```

---

### Customer Extension

```graphql
query {
  customer {
    email
    firstname
    loyalty_points {
      points
      lifetime_points
      tier {
        name
        benefits
      }
    }
  }
}
```

---

### Cart Extension

```graphql
query GetCart($cartId: String!) {
  cart(cart_id: $cartId) {
    id
    loyalty_points_used
    loyalty_discount_amount {
      value
      currency
    }
    available_loyalty_points
    max_redeemable_points
    potential_points_earn
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
```

---

### CustomerOrder Extension

```graphql
query {
  customer {
    orders(pageSize: 10) {
      items {
        number
        total {
          grand_total {
            value
          }
        }
        loyalty_points_earned
        loyalty_points_used
        loyalty_discount_amount {
          value
          currency
        }
      }
    }
  }
}
```

---

## Error Handling

### Error Response Format

**REST:**
```json
{
  "message": "Insufficient points.",
  "code": 400
}
```

**GraphQL:**
```json
{
  "errors": [
    {
      "message": "Insufficient points.",
      "extensions": {
        "category": "graphql-input"
      },
      "locations": [{"line": 2, "column": 3}],
      "path": ["applyLoyaltyPointsToCart"]
    }
  ]
}
```

### Common Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 400 | Insufficient points | Customer doesn't have enough points |
| 400 | Minimum X points required | Points below minimum redemption |
| 401 | Customer not authenticated | Missing or invalid token |
| 403 | Guest customers cannot redeem | Guests not allowed |
| 404 | Customer not found | Invalid customer ID |
| 503 | Loyalty system disabled | System is turned off |

---

## Rate Limiting

API requests are subject to rate limiting:

| Endpoint Type | Limit |
|---------------|-------|
| REST - Read | 100 requests/minute |
| REST - Write | 30 requests/minute |
| GraphQL | 60 requests/minute |

---

## Webhooks

Configure webhooks in Admin > Stores > Configuration > Elsherif > Loyalty System > Webhooks.

### Available Events

| Event | Payload |
|-------|---------|
| `loyalty.points.earned` | `{customer_id, points, order_id, balance}` |
| `loyalty.points.redeemed` | `{customer_id, points, order_id, discount}` |
| `loyalty.points.expired` | `{customer_id, points, expired_count}` |
| `loyalty.tier.changed` | `{customer_id, old_tier, new_tier}` |

---

## SDK Examples

### JavaScript/PWA Studio

```javascript
import { useMutation, useQuery } from '@apollo/client';

// Get loyalty points
const GET_LOYALTY_POINTS = gql`
  query GetLoyaltyPoints {
    customerLoyaltyPoints {
      points
      tier { name }
    }
  }
`;

// Apply points
const APPLY_POINTS = gql`
  mutation ApplyPoints($input: ApplyLoyaltyPointsInput!) {
    applyLoyaltyPointsToCart(input: $input) {
      success
      remaining_balance
    }
  }
`;

function LoyaltyComponent({ cartId }) {
  const { data } = useQuery(GET_LOYALTY_POINTS);
  const [applyPoints] = useMutation(APPLY_POINTS);

  const handleApply = async (points) => {
    await applyPoints({
      variables: {
        input: { cart_id: cartId, points }
      }
    });
  };

  return (
    <div>
      <p>Available: {data?.customerLoyaltyPoints?.points} points</p>
      <button onClick={() => handleApply(100)}>Apply 100 Points</button>
    </div>
  );
}
```

### PHP

```php
<?php
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;

class MyClass
{
    private PointsManagementInterface $pointsManagement;

    public function __construct(PointsManagementInterface $pointsManagement)
    {
        $this->pointsManagement = $pointsManagement;
    }

    public function getCustomerPoints(int $customerId): int
    {
        $balance = $this->pointsManagement->getBalance($customerId);
        return $balance->getPoints();
    }

    public function addBonusPoints(int $customerId, int $points): bool
    {
        return $this->pointsManagement->addPoints(
            $customerId,
            $points,
            'admin',
            null,
            null,
            'Bonus points'
        );
    }
}
```

---

## Changelog

### Version 1.0.0
- Initial release
- REST API endpoints
- GraphQL queries and mutations
- PWA Studio compatibility
- Product, Cart, Customer, Order extensions

---

## Support

For issues or questions:
- GitHub: [Repository URL]
- Email: support@elsherif.com

---

*Documentation last updated: 2024*
