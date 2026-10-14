# Boutique Bakery

CakeOrdering-final is a cake ordering application with cart, checkout, and custom requests.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `cakes`, `cart`, `categories`, `custom_requests`, `expenses`, `orders`, `order_items`, `payment_vouchers`, `users`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Root Pages | [`add_to_cart.php`](add_to_cart.php), [`cart.php`](cart.php), [`checkout.php`](checkout.php), [`custom_requests.php`](custom_requests.php), [`generate_invoice.php`](generate_invoice.php) |
| Admin | [`admin/add_cakes.php`](admin/add_cakes.php), [`admin/add_categories.php`](admin/add_categories.php), [`admin/admin_custom_requests.php`](admin/admin_custom_requests.php), [`admin/admin_expenses.php`](admin/admin_expenses.php), [`admin/admin_home.php`](admin/admin_home.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/CakeOrdering-final.git
cd CakeOrdering-final
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/cake_db.sql`](Database/cake_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`config.php`](config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`css`](css) | Stylesheets |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
