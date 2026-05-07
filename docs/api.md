# Vending Machine API

## Overview

| | |
|---|---|
| **Base URL** | `https://vending-machine.on-forge.com/api/v1` |
| **Format** | JSON (`Content-Type: application/json`) |
| **Auth** | Bearer token via Laravel Sanctum |
| **Prices** | Stored in mills — divide by 1000 for the display value (1500 mills = $1.50) |

---

## Response Envelope

Every response shares a consistent structure.

**Success**
```json
{
  "success": true,
  "message": "Human-readable summary.",
  "data": { }
}
```

**Success with pagination** — `meta` and `links` are added for paginated collections:
```json
{
  "success": true,
  "message": "Human-readable summary.",
  "data": [ ],
  "meta": {
    "current_page": 1,
    "last_page": 4,
    "per_page": 15,
    "total": 60,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "https://vending-machine.on-forge.com/api/v1/products?page=1",
    "last": "https://vending-machine.on-forge.com/api/v1/products?page=4",
    "prev": null,
    "next": "https://vending-machine.on-forge.com/api/v1/products?page=2"
  }
}
```

**Error**
```json
{
  "message": "Unauthenticated."
}
```

**Validation error (422)**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

---

## Rate Limits

| Limiter | Applies to | Limit |
|---|---|---|
| `api-public` | Public product endpoints | 60 req/min per IP |
| `api-auth` | Login | 5 req/min per IP · 3 req/min per email |
| `api-user` | All authenticated endpoints | 120 req/min per user |
| `api-purchase` | Purchase endpoint | 10 req/min per user |

Rate limit headers are included on every response:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
Retry-After: 42          # only present on 429
```

---

## HTTP Status Codes

| Status | Meaning |
|---|---|
| `200` | OK |
| `201` | Created |
| `204` | No content |
| `401` | Unauthenticated — missing or invalid token |
| `403` | Forbidden — authenticated but not authorized (e.g. non-admin) |
| `404` | Resource not found |
| `422` | Validation error or business rule violation |
| `429` | Rate limit exceeded |
| `500` | Server error |

---

## Authentication

### `POST /auth/login`

Exchange credentials for a Sanctum bearer token.

**Rate limit:** `api-auth` — 5 req/min per IP, 3 req/min per email

**Request body**

| Field | Type | Required | Description |
|---|---|---|---|
| `email` | string | Yes | User email |
| `password` | string | Yes | User password |
| `device_name` | string | No | Label for the token (default: `api-token`) |

**Example request**
```bash
curl -X POST https://vending-machine.on-forge.com/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "customer@vending.test",
    "password": "password",
    "device_name": "my-app"
  }'
```

**Example response — 201 Created**
```json
{
  "success": true,
  "message": "Authenticated successfully.",
  "data": {
    "token": "1|abc123xyz...",
    "token_type": "Bearer",
    "user": {
      "id": 2,
      "name": "Customer User",
      "email": "customer@vending.test",
      "role": "customer"
    }
  }
}
```

**Example error — 422 Wrong credentials**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```

**Example error — 429 Rate limit exceeded**
```json
{
  "message": "Too Many Attempts."
}
```

---

### `POST /auth/logout`

Revoke the current bearer token.

**Auth:** Required

**Example request**
```bash
curl -X POST https://vending-machine.on-forge.com/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..."
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Token revoked successfully."
}
```

**Example error — 401 Unauthenticated**
```json
{
  "message": "Unauthenticated."
}
```

---

## Products

### `GET /products`

Paginated product catalog. No authentication required.

**Rate limit:** `api-public` — 60 req/min per IP

**Query parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `search` | string | — | Filter by product name (partial match) |
| `sort` | string | `name` | Sort field: `name`, `price_in_mills`, `quantity_available` |
| `direction` | string | `asc` | Sort direction: `asc`, `desc` |
| `per_page` | integer | `15` | Results per page (max 50) |

**Example request**
```bash
curl "https://vending-machine.on-forge.com/api/v1/products?sort=price_in_mills&direction=asc&per_page=5" \
  -H "Accept: application/json"
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Products retrieved.",
  "data": [
    {
      "id": 12,
      "name": "Cola 330ml",
      "slug": "cola-330ml",
      "price_in_mills": 1500,
      "price": 1.5,
      "quantity_available": 24,
      "stock_status": "in_stock",
      "created_at": "2026-05-07T04:03:21.000000Z",
      "updated_at": "2026-05-07T04:03:21.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 4,
    "per_page": 5,
    "total": 20,
    "from": 1,
    "to": 5
  },
  "links": {
    "first": "https://vending-machine.on-forge.com/api/v1/products?page=1",
    "last": "https://vending-machine.on-forge.com/api/v1/products?page=4",
    "prev": null,
    "next": "https://vending-machine.on-forge.com/api/v1/products?page=2"
  }
}
```

**`stock_status` values**

| Value | Condition |
|---|---|
| `in_stock` | `quantity_available` ≥ 6 |
| `low_stock` | `quantity_available` 1–5 |
| `out_of_stock` | `quantity_available` = 0 |

---

### `GET /products/{slug}`

Single product detail by slug.

**Rate limit:** `api-public` — 60 req/min per IP

**Example request**
```bash
curl "https://vending-machine.on-forge.com/api/v1/products/cola-330ml" \
  -H "Accept: application/json"
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Product retrieved.",
  "data": {
    "id": 12,
    "name": "Cola 330ml",
    "slug": "cola-330ml",
    "price_in_mills": 1500,
    "price": 1.5,
    "quantity_available": 24,
    "stock_status": "in_stock",
    "created_at": "2026-05-07T04:03:21.000000Z",
    "updated_at": "2026-05-07T04:03:21.000000Z"
  }
}
```

**Example error — 404 Not found**
```json
{
  "message": "No query results for model [App\\Models\\Product] cola-999."
}
```

---

### `GET /products/{slug}/recommendations`

Up to 4 in-stock products recommended alongside the given product, ordered by popularity then proximity in price.

**Rate limit:** `api-public` — 60 req/min per IP

**Example request**
```bash
curl "https://vending-machine.on-forge.com/api/v1/products/cola-330ml/recommendations" \
  -H "Accept: application/json"
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Recommendations retrieved.",
  "data": [
    {
      "id": 7,
      "name": "Water 500ml",
      "slug": "water-500ml",
      "price_in_mills": 1000,
      "price": 1.0,
      "quantity_available": 18,
      "stock_status": "in_stock",
      "created_at": "2026-05-07T04:03:21.000000Z",
      "updated_at": "2026-05-07T04:03:21.000000Z"
    },
    {
      "id": 3,
      "name": "Orange Juice 250ml",
      "slug": "orange-juice-250ml",
      "price_in_mills": 2000,
      "price": 2.0,
      "quantity_available": 5,
      "stock_status": "low_stock",
      "created_at": "2026-05-07T04:03:21.000000Z",
      "updated_at": "2026-05-07T04:03:21.000000Z"
    }
  ]
}
```

Returns `"data": []` when no other in-stock products exist.

---

## Orders

### `GET /orders`

Paginated purchase history for the authenticated user.

**Auth:** Required
**Rate limit:** `api-user` — 120 req/min per user

**Example request**
```bash
curl "https://vending-machine.on-forge.com/api/v1/orders" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..."
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Orders retrieved.",
  "data": [
    {
      "id": 1,
      "product_name": "Cola 330ml",
      "product_slug": "cola-330ml",
      "quantity": 1,
      "unit_price_in_mills": 1500,
      "total_amount_in_mills": 1500,
      "status": "completed",
      "purchased_at": "2026-05-07T04:22:20.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1,
    "from": 1,
    "to": 1
  },
  "links": {
    "first": "https://vending-machine.on-forge.com/api/v1/orders?page=1",
    "last": "https://vending-machine.on-forge.com/api/v1/orders?page=1",
    "prev": null,
    "next": null
  }
}
```

When the product has been deleted, `product_name` returns `"Deleted product"` and `product_slug` returns `null`.

**Example error — 401 Unauthenticated**
```json
{
  "message": "Unauthenticated."
}
```

---

### `POST /products/{slug}/buy`

Purchase one unit of a product.

**Auth:** Required
**Rate limit:** `api-user` (120/min) + `api-purchase` (10/min per user)

**Example request**
```bash
curl -X POST "https://vending-machine.on-forge.com/api/v1/products/cola-330ml/buy" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..."
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "You purchased Cola 330ml.",
  "data": {
    "product": {
      "id": 12,
      "name": "Cola 330ml",
      "quantity_available": 23
    }
  }
}
```

**Example error — 422 Out of stock**
```json
{
  "message": "Product is out of stock."
}
```

**Example error — 404 Product not found**
```json
{
  "message": "No query results for model [App\\Models\\Product] cola-999."
}
```

---

## Admin — Products

All admin endpoints require authentication with an admin-role account.

**Auth:** Required (admin)
**Rate limit:** `api-user` — 120 req/min per user

**Example error — 403 Not admin**
```json
{
  "message": "This action is unauthorized."
}
```

---

### `GET /admin/products`

Paginated product list with full detail for admin management.

**Query parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `search` | string | — | Filter by product name |
| `sort` | string | `name` | Sort field: `name`, `price_in_mills`, `quantity_available`, `created_at`, `updated_at` |
| `direction` | string | `asc` | `asc` or `desc` |
| `per_page` | integer | `15` | Results per page (max 100) |

**Example request**
```bash
curl "https://vending-machine.on-forge.com/api/v1/admin/products?sort=created_at&direction=desc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..."
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Products retrieved.",
  "data": [
    {
      "id": 12,
      "name": "Cola 330ml",
      "slug": "cola-330ml",
      "price_in_mills": 1500,
      "price": 1.5,
      "quantity_available": 24,
      "stock_status": "in_stock",
      "created_at": "2026-05-07T04:03:21.000000Z",
      "updated_at": "2026-05-07T04:03:21.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 40,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "https://vending-machine.on-forge.com/api/v1/admin/products?page=1",
    "last": "https://vending-machine.on-forge.com/api/v1/admin/products?page=3",
    "prev": null,
    "next": "https://vending-machine.on-forge.com/api/v1/admin/products?page=2"
  }
}
```

---

### `POST /admin/products`

Create a new product.

**Request body**

| Field | Type | Required | Rules |
|---|---|---|---|
| `name` | string | Yes | Max 255 characters, unique |
| `price` | numeric | Yes | Must be ≥ 0 |
| `quantity_available` | integer | Yes | Must be ≥ 0 |

**Example request**
```bash
curl -X POST "https://vending-machine.on-forge.com/api/v1/admin/products" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..." \
  -d '{
    "name": "Cola 330ml",
    "price": 1.50,
    "quantity_available": 24
  }'
```

**Example response — 201 Created**
```json
{
  "success": true,
  "message": "Product created.",
  "data": {
    "id": 12,
    "name": "Cola 330ml",
    "slug": "cola-330ml",
    "price_in_mills": 1500,
    "price": 1.5,
    "quantity_available": 24,
    "stock_status": "in_stock",
    "created_at": "2026-05-07T10:00:00.000000Z",
    "updated_at": "2026-05-07T10:00:00.000000Z"
  }
}
```

**Example error — 422 Validation**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name has already been taken."],
    "price": ["The price field is required."]
  }
}
```

---

### `GET /admin/products/{id}`

Retrieve a single product by ID.

**Example request**
```bash
curl "https://vending-machine.on-forge.com/api/v1/admin/products/12" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..."
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Product retrieved.",
  "data": {
    "id": 12,
    "name": "Cola 330ml",
    "slug": "cola-330ml",
    "price_in_mills": 1500,
    "price": 1.5,
    "quantity_available": 24,
    "stock_status": "in_stock",
    "created_at": "2026-05-07T04:03:21.000000Z",
    "updated_at": "2026-05-07T04:03:21.000000Z"
  }
}
```

**Example error — 404 Not found**
```json
{
  "message": "No query results for model [App\\Models\\Product] 999."
}
```

---

### `PUT /admin/products/{id}`

Update an existing product. All fields are optional — send only the fields to change.

**Request body**

| Field | Type | Required | Rules |
|---|---|---|---|
| `name` | string | No | Max 255 characters, unique (ignoring current product) |
| `price` | numeric | No | Must be ≥ 0 |
| `quantity_available` | integer | No | Must be ≥ 0 |

**Example request**
```bash
curl -X PUT "https://vending-machine.on-forge.com/api/v1/admin/products/12" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..." \
  -d '{
    "quantity_available": 50
  }'
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Product updated.",
  "data": {
    "id": 12,
    "name": "Cola 330ml",
    "slug": "cola-330ml",
    "price_in_mills": 1500,
    "price": 1.5,
    "quantity_available": 50,
    "stock_status": "in_stock",
    "created_at": "2026-05-07T04:03:21.000000Z",
    "updated_at": "2026-05-07T10:05:00.000000Z"
  }
}
```

---

### `DELETE /admin/products/{id}`

Delete a product. Fails if the product has existing orders.

**Example request**
```bash
curl -X DELETE "https://vending-machine.on-forge.com/api/v1/admin/products/12" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..."
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Product deleted."
}
```

**Example error — 404 Not found**
```json
{
  "message": "No query results for model [App\\Models\\Product] 999."
}
```

---

## Admin — Orders

### `GET /admin/orders`

Paginated list of all customer orders with customer and product details.

**Auth:** Required (admin)
**Rate limit:** `api-user` — 120 req/min per user

**Query parameters**

| Param | Type | Description |
|---|---|---|
| `search` | string | Filter by customer name, customer email, or product name |

**Example request**
```bash
curl "https://vending-machine.on-forge.com/api/v1/admin/orders?search=cola" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc123xyz..."
```

**Example response — 200 OK**
```json
{
  "success": true,
  "message": "Orders retrieved.",
  "data": [
    {
      "id": 1,
      "customer": {
        "id": 2,
        "name": "Customer User",
        "email": "customer@vending.test"
      },
      "product": {
        "id": 12,
        "name": "Cola 330ml",
        "slug": "cola-330ml"
      },
      "quantity": 1,
      "unit_price_in_mills": 1500,
      "total_amount_in_mills": 1500,
      "status": "completed",
      "purchased_at": "2026-05-07T04:22:20.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1,
    "from": 1,
    "to": 1
  },
  "links": {
    "first": "https://vending-machine.on-forge.com/api/v1/admin/orders?page=1",
    "last": "https://vending-machine.on-forge.com/api/v1/admin/orders?page=1",
    "prev": null,
    "next": null
  }
}
```

When the customer account has been deleted, `customer.name` returns `"Deleted user"` and `customer.email` returns `""`. When the product has been deleted, `product.name` returns `"Deleted product"` and `product.slug` returns `null`.
