<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'manage_users',
            'manage_categories',
            'manage_products',
            'view_products',
            'manage_suppliers',
            'view_suppliers',
            'manage_customers',
            'view_customers',
            'manage_purchases',
            'view_purchases',
            'manage_supplier_payment_allocations',
            'view_supplier_payment_allocations',
            'manage_customer_payment_allocations',
            'view_customer_payment_allocations',
            'view_sales',
            'manage_sales',
            'add_sales',
            'edit_sales',
            'pay_sales',
            'add_purchases',
            'edit_purchases',
            'pay_purchases',
            'manage_purchase_returns',
            'view_purchase_returns',
            'add_purchase_returns',
            'edit_purchase_returns',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
