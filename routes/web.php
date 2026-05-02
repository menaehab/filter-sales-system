<?php

use App\Models\ServiceVisit;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */
    Route::livewire('/', 'sales.sale-create')->name('home');

    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */
    Route::livewire('/sales', 'sales.sale-management')
        ->name('sales')
        ->middleware('permission:view_sales|manage_sales|add_sales|edit_sales|pay_sales');

    Route::livewire('/sales/create', 'sales.sale-create')
        ->name('sales.create')
        ->middleware('permission:manage_sales|add_sales');

    Route::livewire('/sales/{sale}', 'sales.sale-show')
        ->name('sales.show')
        ->middleware('permission:view_sales|manage_sales');

    Route::livewire('/sales/{sale}/edit', 'sales.sale-edit')
        ->name('sales.edit')
        ->middleware('permission:manage_sales|edit_sales');

    Route::livewire('/sales/{sale}/print', 'sales.sale-print')
        ->name('sales.print')
        ->middleware('permission:view_sales|manage_sales|add_sales|edit_sales|pay_sales');

    Route::livewire('/overdue-installments', 'sales.overdue-installments')
        ->name('overdue-installments')
        ->middleware('permission:view_overdue_installments');

    Route::livewire('/overdue-installments/print', 'sales.overdue-installments-print')
        ->name('overdue-installments.print')
        ->middleware('permission:view_overdue_installments');

    Route::livewire('/customer-payments/{payment}/print', 'sales.payment-receipt-print')
        ->name('customer-payments.print')
        ->middleware('permission:view_customer_payment_allocations|manage_customer_payment_allocations|pay_sales|manage_sales');

    /*
    |--------------------------------------------------------------------------
    | Categories & Products
    |--------------------------------------------------------------------------
    */
    Route::livewire('/categories', 'categories.category-management')
        ->name('categories')
        ->middleware('permission:manage_categories');

    Route::livewire('/products', 'products.product-management')
        ->name('products')
        ->middleware('permission:view_products|manage_products');

    Route::livewire('/products/{product:slug}', 'products.product-show')
        ->name('products.show')
        ->middleware('permission:view_products|manage_products');

    Route::livewire('/places', 'places.place-management')
        ->name('places')
        ->middleware('permission:view_places|manage_places');

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
    Route::livewire('/users', 'users.user-management')
        ->name('users')
        ->middleware('permission:manage_users');

    /*
    |--------------------------------------------------------------------------
    | Suppliers
    |--------------------------------------------------------------------------
    */
    Route::livewire('/suppliers', 'suppliers.supplier-management')
        ->name('suppliers')
        ->middleware('permission:view_suppliers|manage_suppliers');

    Route::livewire('/suppliers/{supplier:slug}/view', 'suppliers.supplier-view')
        ->name('suppliers.view')
        ->middleware('permission:view_suppliers|manage_suppliers');

    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */
    Route::livewire('/customers', 'customers.customer-management')
        ->name('customers')
        ->middleware('permission:view_customers|manage_customers');

    Route::livewire('/customers/{customer:slug}/view', 'customers.customer-view')
        ->name('customers.view')
        ->middleware('permission:view_customers|manage_customers');

    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */
    Route::livewire('/purchases', 'purchases.purchase-management')
        ->name('purchases')
        ->middleware('permission:view_purchases|manage_purchases|add_purchases|edit_purchases|pay_purchases');

    Route::livewire('/purchases/create', 'purchases.purchase-create')
        ->name('purchases.create')
        ->middleware('permission:manage_purchases|add_purchases');

    Route::livewire('/purchases/{purchase}', 'purchases.purchase-show')
        ->name('purchases.show')
        ->middleware('permission:view_purchases|manage_purchases');

    Route::livewire('/purchases/{purchase}/edit', 'purchases.purchase-edit')
        ->name('purchases.edit')
        ->middleware('permission:manage_purchases|edit_purchases');

    Route::livewire('/purchases/{purchase}/print', 'purchases.purchase-print')
        ->name('purchases.print')
        ->middleware('permission:view_purchases|manage_purchases|add_purchases|edit_purchases|pay_purchases');

    Route::livewire('/supplier-payments/{payment}/print', 'purchases.payment-voucher-print')
        ->name('supplier-payments.print')
        ->middleware('permission:view_supplier_payment_allocations|manage_supplier_payment_allocations|pay_purchases|manage_purchases');

    /*
    |--------------------------------------------------------------------------
    | Supplier Payments
    |--------------------------------------------------------------------------
    */
    Route::livewire('/supplier-payments', 'supplier-payments.supplier-payment-management')
        ->name('supplier-payments')
        ->middleware('permission:view_supplier_payment_allocations|manage_supplier_payment_allocations');

    /*
    |--------------------------------------------------------------------------
    | Customers Payments
    |--------------------------------------------------------------------------
    */
    Route::livewire('/customer-payments', 'customer-payments.customer-payment-management')
        ->name('customer-payments')
        ->middleware('permission:view_customer_payment_allocations|manage_customer_payment_allocations');

    /*
    |--------------------------------------------------------------------------
    | Purchase Returns
    |--------------------------------------------------------------------------
    */
    Route::livewire('/purchase-returns', 'purchase-returns.purchase-return-management')
        ->name('purchase-returns')
        ->middleware('permission:view_purchase_returns|manage_purchase_returns|add_purchase_returns|edit_purchase_returns');

    Route::livewire('/purchase-returns/create', 'purchase-returns.purchase-return-create')
        ->name('purchase-returns.create')
        ->middleware('permission:manage_purchase_returns|add_purchase_returns');

    Route::livewire('/purchase-returns/{purchaseReturn}', 'purchase-returns.purchase-return-show')
        ->name('purchase-returns.show')
        ->middleware('permission:view_purchase_returns|manage_purchase_returns');

    Route::livewire('/purchase-returns/{purchaseReturn}/edit', 'purchase-returns.purchase-return-edit')
        ->name('purchase-returns.edit')
        ->middleware('permission:manage_purchase_returns|edit_purchase_returns');

    /*
    |--------------------------------------------------------------------------
    | Sale Returns
    |--------------------------------------------------------------------------
    */
    Route::livewire('/sale-returns', 'sale-returns.sale-return-management')
        ->name('sale-returns')
        ->middleware('permission:view_sale_returns|manage_sale_returns|add_sale_returns|edit_sale_returns');

    Route::livewire('/sale-returns/create', 'sale-returns.sale-return-create')
        ->name('sale-returns.create')
        ->middleware('permission:manage_sale_returns|add_sale_returns');

    Route::livewire('/sale-returns/{saleReturn}', 'sale-returns.sale-return-show')
        ->name('sale-returns.show')
        ->middleware('permission:view_sale_returns|manage_sale_returns');

    Route::livewire('/sale-returns/{saleReturn}/edit', 'sale-returns.sale-return-edit')
        ->name('sale-returns.edit')
        ->middleware('permission:manage_sale_returns|edit_sale_returns');

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    Route::livewire('/filters', 'filters.filter-management')
        ->name('filters')
        ->middleware('permission:view_water_filters|manage_water_filters');

    Route::livewire('/filters/{filter:slug}', 'filters.filter-view')
        ->name('filters.view')
        ->middleware('permission:view_water_filters|manage_water_filters');

    Route::livewire('/service-visits', 'filters.service-visit-management')
        ->name('service-visits')
        ->middleware('permission:view_service_visits|manage_service_visits');

    Route::get('/service-visits/print/pending', function () {
        $visits = ServiceVisit::query()
            ->with(['waterFilter.customer.phones'])
            ->where('is_completed', false)
            ->latest('created_at')
            ->get();

        return view('livewire.filters.service-visits-print', compact('visits'));
    })
        ->name('service-visits.print.pending')
        ->middleware('permission:view_service_visits|manage_service_visits');

    Route::get('/service-visits/print/selected', function (Request $request) {
        $ids = explode(',', $request->query('ids', ''));
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return redirect()->route('service-visits');
        }

        $visits = ServiceVisit::query()
            ->with(['waterFilter.customer.phones'])
            ->whereIn('id', $ids)
            ->latest('created_at')
            ->get();

        return view('livewire.filters.service-visits-print', compact('visits'));
    })
        ->name('service-visits.print.selected')
        ->middleware('permission:view_service_visits|manage_service_visits');

    Route::livewire('/service-visits/{serviceVisit}', 'filters.service-visit-show')
        ->name('service-visits.show')
        ->middleware('permission:view_service_visits|manage_service_visits');

    /*
    |--------------------------------------------------------------------------
    | Damaged Products
    |--------------------------------------------------------------------------
    */
    Route::livewire('/damaged-products', 'damaged-products.damaged-product-management')
        ->name('damaged-products')
        ->middleware('permission:view_damaged_products|manage_damaged_products');

    /*
    |--------------------------------------------------------------------------
    | Expenses
    |--------------------------------------------------------------------------
    */
    Route::livewire('/expenses', 'expenses.expense-management')
        ->name('expenses')
        ->middleware('permission:view_expenses|manage_expenses');

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::livewire('/dashboard', 'dashboard')
        ->middleware('permission:view_dashboard')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    Route::livewire('/activities', 'activity-log.activity-log-management')
        ->middleware('permission:view_activities')
        ->name('activities');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    Route::delete('/notifications/delete-all-read', [NotificationController::class, 'deleteAllRead'])
        ->name('notifications.delete-all-read');

    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])
        ->name('notifications.delete-all');

    Route::delete('/notifications/{id}', [NotificationController::class, 'delete'])
        ->name('notifications.delete');
});
