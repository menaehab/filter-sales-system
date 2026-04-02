# Filter Sales System - Full Documentation, Deep Code Review, and Professional Evaluation

## Review Context

- Project type: Laravel 12 + Livewire 4 business system
- Date of review: 2026-04-01
- Branch reviewed: `feature/maintenances`
- Test execution evidence: `php artisan test --parallel` -> 196 passed, 809 assertions

---

## 1. System Overview

Filter Sales System manages end-to-end operations for a filter sales business:

- Sales and purchase invoice lifecycle
- Installment and cash payments with allocation tables
- Sales returns and purchase returns
- Product stock updates and movement ledger tracking
- Damaged products and operating expenses
- Customer/supplier credit and balance tracking
- Water filter installation/readings/maintenance/candle replacement
- Daily operational reminders and low-stock alerts

The codebase is domain-oriented and mostly modular. Main business domains are implemented in parallel across Livewire pages, actions, models, and tests.

---

## 2. Architecture (Laravel MVC + Livewire Flow)

## 2.1 High-Level Structure

- Routes: `routes/web.php` maps mostly to Livewire pages using `Route::livewire(...)`
- UI orchestration: `app/Livewire/*`
- Domain operations: `app/Actions/*`
- Data layer: `app/Models/*`
- Validation layer: `app/Http/Requests/*`
- Metrics/reporting: `app/Services/StatisticsService.php`
- Side effects and numbering: model observers in `app/Observers/*`

## 2.2 Request/Execution Flow

Typical write flow:

1. Browser hits a route in `routes/web.php`
2. Livewire component loads page state
3. User submits form in the same component
4. Component validates (FormRequest rules are often mapped into `form.*` or direct arrays)
5. Component calls an Action class for persistence
6. Action executes transactional writes (models + allocations + product movements)
7. Component redirects and flash messages update UI

## 2.3 Where MVC Shows Up In A Livewire App

- Model: Eloquent models with relationships/computed attributes
- Controller-like orchestration: Livewire components (instead of classic controllers)
- View: Blade + Livewire views

Only notification endpoints use a traditional controller (`NotificationController`).

---

## 3. Livewire Components

This section lists each component, what it does, and how it interacts with backend layers.

## 3.1 Core and System

- `app/Livewire/Dashboard.php`
    - Purpose: KPI dashboard and charts.
    - Backend interaction: heavy use of `StatisticsService` plus direct aggregate queries on Product/Category/Customer/Supplier.

- `app/Livewire/ActivityLog/ActivityLogManagement.php`
    - Purpose: browse/filter activity logs.
    - Backend interaction: queries `Spatie\Activitylog\Models\Activity`, joins causer/subject relations, supports date/event/model/user filters.

- `app/Livewire/Users/UserManagement.php`
    - Purpose: manage users, roles, permissions, place assignments.
    - Backend interaction: uses `CreateUserAction`, `UpdateUserAction`, `DeleteUserAction`; syncs Spatie permissions and `place_user` pivot.

- `app/Livewire/Actions/Logout.php`
    - Purpose: logout action class used by Livewire UI.
    - Backend interaction: calls Auth logout and session invalidation.

## 3.2 Sales Domain

- `app/Livewire/Sales/SaleCreate.php`
    - Purpose: POS-style sale creation with optional filter/water-reading capture.
    - Backend interaction: uses `CreateSaleAction`; dynamic validation from `CreateSaleRequest`; inline creation for customer/place/filter.

- `app/Livewire/Sales/SaleEdit.php`
    - Purpose: edit sale items/pricing/installments.
    - Backend interaction: uses `UpdateSaleAction`; recalculates totals with `SalePriceCalculator`.

- `app/Livewire/Sales/SaleManagement.php`
    - Purpose: searchable sales list, status filters, payment modal, delete action.
    - Backend interaction: query trait + raw aggregate filters; payment creation delegated to `CreateCustomerPaymentAction`; delete via `DeleteSaleAction`.

- `app/Livewire/Sales/SaleShow.php`
    - Purpose: sale details and payment entry.
    - Backend interaction: delegates payment creation to `CreateCustomerPaymentAction`.

- `app/Livewire/Sales/SalePrint.php`
    - Purpose: print sale invoice.
    - Backend interaction: loads sale relations for print view.

- `app/Livewire/Sales/PaymentReceiptPrint.php`
    - Purpose: print customer payment receipt.
    - Backend interaction: loads payment with allocations.

## 3.3 Purchase Domain

- `app/Livewire/Purchases/PurchaseCreate.php`
    - Purpose: create purchase with inline supplier/category/product creation.
    - Backend interaction: uses `CreatePurchaseAction`; validates via `CreatePurchaseRequest`; computes applied supplier credit/installments.

- `app/Livewire/Purchases/PurchaseEdit.php`
    - Purpose: update purchase items/supplier/payment terms.
    - Backend interaction: uses `UpdatePurchaseAction`; supports inline supplier/category/product creation.

- `app/Livewire/Purchases/PurchaseManagement.php`
    - Purpose: list/filter purchases, register payments, delete purchases.
    - Backend interaction: uses `CreateSupplierPaymentAction` for payments and `DeletePurchaseAction` for deletes.

- `app/Livewire/Purchases/PurchaseShow.php`
    - Purpose: purchase detail with payment modal.
    - Backend interaction: delegates payment creation to `CreateSupplierPaymentAction`.

- `app/Livewire/Purchases/PurchasePrint.php`
    - Purpose: print purchase invoice.
    - Backend interaction: loads purchase relations for print view.

- `app/Livewire/Purchases/PaymentVoucherPrint.php`
    - Purpose: print supplier payment voucher.
    - Backend interaction: loads payment + allocations + purchase data.

## 3.4 Sale Return Domain

- `app/Livewire/SaleReturns/SaleReturnCreate.php`
    - Purpose: create sale return from sale number and selected items.
    - Backend interaction: uses `CreateSaleReturnAction` (creates return items, updates stock, logs movements).

- `app/Livewire/SaleReturns/SaleReturnEdit.php`
    - Purpose: edit existing sale return quantities/reason/refund mode.
    - Backend interaction: component performs transaction directly (reverse/re-apply stock, recreate movements).

- `app/Livewire/SaleReturns/SaleReturnManagement.php`
    - Purpose: list sale returns and delete.
    - Backend interaction: delete logic is in component (stock decrement + movement delete + return delete).

- `app/Livewire/SaleReturns/SaleReturnShow.php`
    - Purpose: view sale return details.
    - Backend interaction: relation loading only.

## 3.5 Purchase Return Domain

- `app/Livewire/PurchaseReturns/PurchaseReturnCreate.php`
    - Purpose: create purchase return.
    - Backend interaction: uses `CreatePurchaseReturnAction` (stock decrement + negative movement).

- `app/Livewire/PurchaseReturns/PurchaseReturnEdit.php`
    - Purpose: edit purchase return.
    - Backend interaction: component transaction directly manages stock and movement recreation.

- `app/Livewire/PurchaseReturns/PurchaseReturnManagement.php`
    - Purpose: list and delete purchase returns.
    - Backend interaction: delegates delete flow to `DeletePurchaseReturnAction` (restores stock, deletes movement records, deletes return).

- `app/Livewire/PurchaseReturns/PurchaseReturnShow.php`
    - Purpose: show purchase return detail.
    - Backend interaction: relation loading only.

## 3.6 Payments

- `app/Livewire/CustomerPayments/CustomerPaymentManagement.php`
    - Purpose: browse customer payments and delete payment records.
    - Backend interaction: searchable payment query via relations (`customer`, `user`, `allocations.sale`).

- `app/Livewire/SupplierPayments/SupplierPaymentManagement.php`
    - Purpose: browse supplier payments and delete payment records.
    - Backend interaction: uses `DeleteSupplierPaymentAction` and relational search.

## 3.7 People and Inventory Master Data

- `app/Livewire/Customers/CustomerManagement.php`
    - Purpose: CRUD customers.
    - Backend interaction: uses customer actions + place relation.

- `app/Livewire/Customers/CustomerView.php`
    - Purpose: customer profile tabs (sales, payments, returns, filters).
    - Backend interaction: relationship queries with pagination.

- `app/Livewire/Suppliers/SupplierManagement.php`
    - Purpose: CRUD suppliers.
    - Backend interaction: supplier actions.

- `app/Livewire/Suppliers/SupplierView.php`
    - Purpose: supplier profile tabs (purchases, payments, returns).
    - Backend interaction: relationship queries with pagination.

- `app/Livewire/Suppliers/SupplierDetails.php`
    - Purpose: supplier contact/status summary page.
    - Backend interaction: supplier model only.

- `app/Livewire/Categories/CategoryManagement.php`
    - Purpose: CRUD categories.
    - Backend interaction: category actions + request classes.

- `app/Livewire/Products/ProductManagement.php`
    - Purpose: CRUD products with category/stock/maintenance filters.
    - Backend interaction: product actions + category relation query.

- `app/Livewire/Products/ProductShow.php`
    - Purpose: product movement analytics and profitability summary.
    - Backend interaction: aggregates via `product_movements`, `sale_items`, `damaged_products`.

- `app/Livewire/Places/PlaceManagement.php`
    - Purpose: CRUD places and place-user assignments.
    - Backend interaction: place actions + `place_user` pivot sync.

## 3.8 Filter and Maintenance Domain

- `app/Livewire/Filters/FilterManagement.php`
    - Purpose: CRUD water filters and customer linkage.
    - Backend interaction: water filter actions + customer relation search.

- `app/Livewire/Filters/FilterView.php`
    - Purpose: filter detail page for readings, candle replacement, maintenance items.
    - Backend interaction: direct transactions with `Maintenance`, `MaintenanceItem`, `WaterReading`, and `WaterFilter::markCandlesReplaced`.

## 3.9 Damage and Expense

- `app/Livewire/DamagedProducts/DamagedProductManagement.php`
    - Purpose: manage damaged product records and stock impact.
    - Backend interaction: damaged product actions with stock + movement updates.

- `app/Livewire/Expenses/ExpenseManagement.php`
    - Purpose: manage expenses and expense totals.
    - Backend interaction: expense actions + filtered sum query.

---

## 4. Database Design

## 4.1 Core Tables

- `users`: identity, optional role field, Fortify 2FA columns
- `places`: locations
- `place_user`: many-to-many between users and places
- `categories`: product categories
- `products`: inventory items with `quantity`, `min_quantity`, `for_maintenance`

## 4.2 Sales Side

- `sales` -> belongs to `customers` and `users`
- `sale_items` -> belongs to `sales` and `products`
- `customer_payments` -> belongs to `customers` and `users`
- `customer_payment_allocations` -> links customer payments to sales
- `sale_returns` -> belongs to `sales` and `users`
- `sale_return_items` -> belongs to `sale_returns` and `products`

## 4.3 Purchase Side

- `suppliers`
- `purchases` -> belongs to `suppliers` and `users`
- `purchase_items` -> belongs to `purchases` and `products`
- `supplier_payments` -> belongs to `suppliers` and `users`
- `supplier_payment_allocations` -> links supplier payments to purchases
- `purchase_returns` -> belongs to `purchases` and `users`
- `purchase_return_items` -> belongs to `purchase_returns` and `products`

## 4.4 Inventory and Operations

- `product_movements` (polymorphic: `movable_type`, `movable_id`) for stock audit trail
- `damaged_products` -> belongs to `products`, `users`
- `expenses` -> belongs to `users`

## 4.5 Filter and Maintenance

- `water_filters` -> belongs to `customers`
- `water_readings` -> belongs to `water_filters`
- `maintenances` -> belongs to `water_filters`, `users`
- `maintenance_items` -> belongs to `maintenances`, `sale_items`
- `water_filter_candle_changes` -> belongs to `water_filters`, optional `users`, optional `maintenances`

## 4.6 Eloquent Relationship Summary

- Category hasMany Product
- Product belongsTo Category, hasMany ProductMovement
- Customer belongsTo Place, hasMany Sale, CustomerPayment, WaterFilter
- Supplier hasMany Purchase, SupplierPayment
- Sale hasMany SaleItem, CustomerPaymentAllocation, SaleReturn, morphMany ProductMovement
- Purchase hasMany PurchaseItem, SupplierPaymentAllocation, PurchaseReturn, morphMany ProductMovement
- SaleReturn/PurchaseReturn each have items + morphMany ProductMovement
- Maintenance hasMany MaintenanceItem and WaterFilterCandleChange
- WaterFilter hasMany WaterReading, Maintenance, WaterFilterCandleChange

---

## 5. Request Lifecycle

## 5.1 Sale Creation Lifecycle

1. UI at `/sales/create` mounts `SaleCreate`.
2. User updates cart/payment/filter inputs.
3. Component validates with mapped rules from `CreateSaleRequest`.
4. `CreateSaleAction` runs transaction:
    - create sale
    - create sale items
    - decrement product stock
    - write `product_movements`
    - create customer payment + allocations for down payment/credit
    - optional filter/water-reading creation
5. Sale observer sets invoice number and user defaults.
6. Component redirects to list or print route.

## 5.2 Purchase Return Lifecycle

1. UI opens return creation/edit component.
2. Selected item quantities validated.
3. Action/component writes return header + items.
4. Stock and movement entries are adjusted.
5. Redirect to management page.

## 5.3 Filter Maintenance Lifecycle

1. Filter detail page validates selected candles and requested maintenance items.
2. Transaction creates maintenance + item allocations from sold maintenance products.
3. Candle replacement dates and change logs are written.
4. UI refreshes readings/candle statuses/maintenance history.

---

## 6. Routing

## 6.1 Web Route Design

- Main routes are grouped under `auth` middleware.
- Most pages are mapped with `Route::livewire` aliases by domain.
- Permission middleware is applied per route (`permission:...`).

## 6.2 Route Model Binding

- `Sale` and `Purchase` bind by `number` (`getRouteKeyName`)
- Many master entities bind by `slug` (`Customer`, `Supplier`, `Product`, `WaterFilter`, `Place`)

## 6.3 Identified Routing Issue

- Route alias mismatch has been resolved.
- Customer detail route now maps to existing `customers.customer-view` component (`app/Livewire/Customers/CustomerView.php`).

---

## 7. Authentication and Authorization

## 7.1 Authentication

- Guard: `web` (session)
- Fortify integration:
    - custom login using email OR phone (`FortifyServiceProvider::authenticateUsing`)
    - strict rate limiting for login and 2FA
    - registration/reset/2FA challenge views are disabled with 404 in this UI profile

## 7.2 Authorization

- Spatie Permission middleware aliases registered in `bootstrap/app.php`
- Permission list seeded in `database/seeders/PermissionSeeder.php`
- Route-level permission checks are broad and mostly consistent
- Many Livewire write actions also call `abort_unless(...can(...))`

## 7.3 Security Gap

- FormRequest authorization has been hardened with permission-aware checks in request classes.
- There are no policy classes in `app/Policies`.
- Authorization is enforced at middleware, component, and request layers.

---

## 8. Business Logic

## 8.1 Pricing, Discount, VAT, Installments

- Sale pricing is centralized in `App\Support\SalePriceCalculator`
- Supports discount, optional VAT, installment surcharge, interest, and applied customer credit

## 8.2 Balance and Credit

- Customer and supplier balances are derived from purchases/sales, payments, and non-cash returns
- `available_credit` is derived from negative balance

## 8.3 Stock and Movement Integrity

- Stock quantity is updated directly on `products.quantity`
- Each stock-affecting operation should also persist a `product_movements` record

## 8.4 Numbering Strategy

- Observers auto-generate document numbers (`YYYYMMDD-###`) for:
    - sales
    - purchases
    - sale returns
    - purchase returns

## 8.5 Reminder Automation

Daily scheduled commands:

- supplier installments
- customer installments
- low stock alerts
- filter candle reminders

---

## 9. Validation

Validation style is mixed but generally robust:

- FormRequest classes exist for all major modules
- Livewire components frequently map FormRequest rules into `form.*` or custom field paths
- Dynamic arrays (cart/items) are validated with indexed rules
- Additional inline rules are used for dynamic maintenance/filter behavior

Strength: good rule coverage across modules.

Historical weakness (now addressed): some request classes had stale contracts (for example `UpdateSaleRequest` previously used `cart.*` while `SaleEdit` used `items.*`).

Current state: `UpdateSaleRequest` is aligned to the `items.*` contract used in `SaleEdit`.

---

## 10. Error Handling

Implemented patterns:

- `findOrFail` and `abort_unless` guard invalid access quickly
- transactions for most high-impact write flows
- explicit Livewire validation feedback (`assertHasErrors` in tests)

Gaps:

- no centralized domain exception strategy
- some flows keep critical invariants in components, increasing risk of accidental bypass

---

## 11. Deployment (Laravel Best Practices)

Recommended production checklist:

1. Environment and config
    - set `APP_ENV=production`, `APP_DEBUG=false`
    - configure DB, queue, cache, mail
2. Optimize runtime
    - `php artisan config:cache`
    - `php artisan route:cache`
    - `php artisan view:cache`
3. Build frontend
    - `npm ci && npm run build`
4. Migrate safely
    - `php artisan migrate --force`
5. Queue and scheduler
    - run queue worker (`php artisan queue:work`)
    - run scheduler (`php artisan schedule:work` or server cron)
6. Monitoring
    - centralize logs
    - track failed jobs
    - monitor notification throughput

---

## 12. Deep Code Review

Findings are ordered by severity and now reflect current post-fix status.

## 12.1 Resolved Findings

### H-01 Broken Customer Details Route Alias (Resolved)

- Route now uses the existing customer detail page alias (`customers.customer-view`) in `routes/web.php`.

### H-02 Purchase Delete Inventory/Movement Drift (Resolved)

- `DeletePurchaseAction` now runs full rollback inside a transaction:
    - decrements product quantities for each purchase item
    - deletes `product_movements` rows for the purchase
    - deletes orphan supplier payments and purchase record

### H-03 Purchase Return Delete Orphan Movements (Resolved)

- `DeletePurchaseReturnAction` now restores stock, deletes purchase-return movements, and deletes the return in one transaction.
- `PurchaseReturnManagement` delegates deletion to the action.

### H-04 SaleReturn Test Suite Misalignment (Resolved)

- `tests/Feature/SaleReturn/*` now targets real sale-return models/routes/components.
- Purchase-return coverage remains under `tests/Feature/PurchaseReturn/*`.

### M-01 Sidebar Permission Typo (Resolved)

- Sidebar checks `manage_purchase_returns` (correct seeded permission key).

### M-02 WaterFilter Action Field Drift (Resolved)

- Water filter create/update actions write schema-accurate keys (`installed_at`) and no longer rely on stale field names.

### M-03 MaintenanceItem Invalid Relation (Resolved)

- Misleading `product()` relation removed from `MaintenanceItem`.
- Product access is via `saleItem->product`.

### M-04 UpdateSale Validation Contract Drift (Resolved)

- `UpdateSaleRequest` now validates `items.*` to match `SaleEdit` contract.

### M-05 Payment Creation Duplication (Resolved)

- Payment/allocation creation for sale and purchase flows is centralized in action classes.
- Livewire components call actions instead of writing payment rows inline.

### M-06 FormRequest Authorization Bypass (Resolved)

- FormRequest `authorize()` methods now apply permission-aware checks (no blanket `return true`).

### L-01 Unused/Broken SaleService (Resolved)

- `app/Services/SaleService.php` has been removed.

### L-02 Misnamed `User::users()` Relation (Resolved)

- Misnamed duplicate relation removed; proper `supplierPayments()` relation remains.

## 12.2 Remaining Finding

### L-03 Portability/Scale Concerns In Reminder Commands (Open)

- Evidence:
    - some commands still notify all users (`User::all()`) instead of scoped recipients.
    - supplier reminder still uses MySQL-specific `DATE_ADD` raw SQL.
- Impact: noisy notifications at scale and reduced DB portability.
- Recommendation: target permission-based recipients and use DB-agnostic date filtering logic.

---

## 13. Problems (Current Open List)

1. Reminder commands still use broad recipients (`User::all()`) instead of permission-scoped recipients.
2. Supplier installment reminder still depends on MySQL-specific `DATE_ADD` SQL.

---

## 14. Improvements (Status Matrix)

| Finding | Status   | Implementation Summary                                                                             |
| ------- | -------- | -------------------------------------------------------------------------------------------------- |
| H-01    | Resolved | customer detail route now maps to `customers.customer-view`.                                       |
| H-02    | Resolved | purchase delete now restores stock, removes movements, and handles orphan payments in transaction. |
| H-03    | Resolved | purchase-return delete centralized in action with movement cleanup.                                |
| H-04    | Resolved | SaleReturn test suite rewritten to actual sale-return domain.                                      |
| M-01    | Resolved | sidebar permission key corrected to `manage_purchase_returns`.                                     |
| M-02    | Resolved | water-filter actions aligned to schema keys (`installed_at`).                                      |
| M-03    | Resolved | invalid `MaintenanceItem::product()` relation removed.                                             |
| M-04    | Resolved | update-sale request contract aligned to `items.*`.                                                 |
| M-05    | Resolved | payment creation/allocation centralized through action classes.                                    |
| M-06    | Resolved | request authorization now permission-aware.                                                        |
| L-01    | Resolved | unused `SaleService` removed.                                                                      |
| L-02    | Resolved | misnamed `User::users()` relation removed.                                                         |
| L-03    | Open     | reminder command portability and recipient-scoping still need hardening.                           |

---

## 15. Final Evaluation

## 15.1 Score (Out of 10)

**8.7 / 10**

Reasoning:

- Strong: domain modularization, transactional invariants in key delete/payment flows, route integrity, and improved request-layer authorization.
- Remaining weakness: reminder command portability and recipient targeting can still be improved.

## 15.2 Team Level Assessment

**Mid-level implementation (approaching senior patterns in some areas).**

Senior signals present:

- good domain modularization
- observer-based numbering
- financial allocation model design

Remaining gap to senior consistency bar:

- reminder notification targeting/scoping at scale
- database portability in date arithmetic for scheduled reminders

## 15.3 Is It Production Ready?

**Production-ready for core transactional domains, with minor scheduler hardening recommended.**

## 15.4 What Is Missing To Reach Senior-Level Quality

1. Replace broad reminder recipients with permission-scoped recipients.
2. Refactor reminder date logic to DB-agnostic query patterns.
3. Add CI checks for architecture drift (route-component contracts, request authorization coverage).
4. Continue optimizing reporting/balance queries for larger datasets.

---

## Appendix: Route and Scheduler Notes

### Route Surface

- Primary app routes are in `routes/web.php` and protected by `auth` + permissions.
- Notification endpoints are controller-based under the same auth group.

### Scheduler Surface

- Daily commands configured in `routes/console.php`:
    - `installments:remind`
    - `filters:candle-remind`
    - `customers:installment-remind`
    - `products:low-stock-alert`
