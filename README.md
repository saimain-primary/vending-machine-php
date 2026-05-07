# Vending Machine

A digital vending machine POS system built with Laravel 13 and Inertia.js. Customers browse a live product catalog, check stock and pricing, and complete purchases in as few steps as possible. Admins manage inventory separately.

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4 · Laravel 13 |
| Frontend | Vue 3 · Inertia.js v3 · Tailwind CSS v4 |
| Auth | Laravel Sanctum |
| Testing | Pest v4 |

## Getting Started

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

Production URL: `https://vending-machine.on-forge.com/`.

## Seeded Accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@vending.test` | `password` |
| Customer | `customer@vending.test` | `password` |

## Running Tests

```bash
php artisan test --compact
```

## API

A versioned REST API is available for external frontend integrations.

- **Base URL:** `https://vending-machine.on-forge.com/api/v1`
- **Auth:** Bearer token (Sanctum)
- **Docs:** [docs/api.md](docs/api.md)

### Quick Reference

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/auth/login` | — | Get bearer token |
| `POST` | `/auth/logout` | Required | Revoke token |
| `GET` | `/products` | — | List products |
| `GET` | `/products/{slug}` | — | Product detail |
| `GET` | `/products/{slug}/recommendations` | — | Related products |
| `POST` | `/products/{slug}/buy` | Required | Purchase product |
| `GET` | `/orders` | Required | My order history |
| `GET` | `/admin/products` | Admin | List all products |
| `POST` | `/admin/products` | Admin | Create product |
| `GET` | `/admin/products/{id}` | Admin | Get product |
| `PUT` | `/admin/products/{id}` | Admin | Update product |
| `DELETE` | `/admin/products/{id}` | Admin | Delete product |
| `GET` | `/admin/orders` | Admin | List all orders |
