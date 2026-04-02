# Filter Sales System

Filter Sales System is a Laravel + Livewire business platform for managing the full commercial lifecycle of water filters and related operations.

## Project Description

### Problem

Small and medium businesses that sell and maintain water filters often run operations in fragmented tools: separate sheets for sales, notebook tracking for supplier debt, manual stock counts, and ad-hoc maintenance reminders.

### Solution

This project centralizes sales, purchases, returns, payments, product stock movements, customer/supplier balances, filter maintenance records, and automated reminders in one permission-aware web system.

## Features

- POS-style sales creation with cash/installment modes
- Purchase creation with installment support and supplier credit application
- Sales and purchase returns with inventory impact
- Customer and supplier payment allocations linked to invoices
- Product movement ledger (purchase, sale, return, damaged)
- Water filter lifecycle management (readings, candle replacement, maintenance)
- Dashboard metrics and trend analytics
- Role and permission based access control (Spatie Permission)
- Activity logging for auditable operations
- Daily reminder commands for installments, low stock, and filter candles

## Tech Stack

- Backend: Laravel 12, PHP 8.2+
- Frontend: Livewire 4, Blade
- Styling/Build: Tailwind CSS 4, Vite 7
- Authentication: Laravel Fortify (email or phone login)
- Authorization: Spatie Laravel Permission
- Audit Logging: Spatie Activitylog
- Slugs: Spatie Sluggable
- Testing: Pest 4
- Database: MySQL/MariaDB (recommended)

## Installation

1. Clone the repository.

```bash
git clone <repository-url>
cd filter-sales-system
```

2. Install PHP dependencies.

```bash
composer install
```

3. Create and configure environment file.

```bash
cp .env.example .env
php artisan key:generate
```

4. Update database credentials in `.env`.

5. Run migrations and seeders.

```bash
php artisan migrate --seed
```

6. Install frontend dependencies.

```bash
npm install
```

7. Run the application in development mode.

```bash
composer run dev
```

Alternative split services:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

## Usage

1. Sign in with an authorized account.
2. Configure master data (categories, products, places, users).
3. Manage suppliers/customers.
4. Create purchases and sales.
5. Register payments and returns.
6. Track maintenance and candle replacements in filters.
7. Monitor dashboard KPIs and low-stock/reminder notifications.

### Default seeded admin account

Created by `database/seeders/AdminSeeder.php`:

- Email: `admin@admin.com`
- Password: `password`

Change seeded credentials outside local development.

## Livewire Implementation

This is a Livewire-first application where most pages are mounted through `Route::livewire(...)` in `routes/web.php`.

- Livewire components handle UI state, pagination, filters, and modals.
- Components delegate critical business operations to Action classes in `app/Actions`.
- Models encapsulate relationships and domain calculations.
- Shared traits (`app/Livewire/Traits`) standardize CRUD/query/form behavior across modules.

Example flow:

1. User submits sale form in `app/Livewire/Sales/SaleCreate.php`.
2. Component validates input using FormRequest rules.
3. Component calls `App\Actions\Sales\CreateSaleAction`.
4. Action runs DB transaction, persists sale/items/payments, and writes product movements.
5. UI redirects to print or list page.

## Folder Structure

```text
app/
	Actions/                 # Business operation classes (transaction boundaries)
	Http/
		Requests/              # Validation rules
	Livewire/                # Page components and UI orchestration
	Models/                  # Eloquent models and domain attributes
	Observers/               # Auto-numbering and model lifecycle hooks
	Services/                # Dashboard/statistics services
bootstrap/
config/
database/
	migrations/
	seeders/
resources/
	views/
routes/
tests/
```

## Testing and Quality

Run tests:

```bash
php artisan test --parallel
```

Run style checks:

```bash
composer run lint:check
```

Format code style:

```bash
composer run lint
```

## Scheduled Jobs

Scheduled in `routes/console.php`:

- `installments:remind`
- `filters:candle-remind`
- `customers:installment-remind`
- `products:low-stock-alert`

Production scheduler command:

```bash
php artisan schedule:work
```

## Future Improvements

- Centralize all inventory invariants in action layer (especially delete flows)
- Add policy-based authorization for defense-in-depth
- Strengthen test coverage for sale-return flows and route-contract drift
- Optimize dashboard/statistics queries to reduce N+1 patterns
- Add CI pipeline with static analysis and architecture tests
- Add API layer for integrations/report exports

## Full Documentation

See the complete architecture, database documentation, deep code review, problem list, and professional evaluation in:

- `docs/PROJECT_DOCUMENTATION.md`
