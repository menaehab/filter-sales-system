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
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
