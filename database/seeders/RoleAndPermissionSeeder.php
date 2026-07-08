<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::findOrCreate('manage campaigns');
        Permission::findOrCreate('approve campaigns');
        Permission::findOrCreate('manage users');
        Permission::findOrCreate('review verifications');
        Permission::findOrCreate('manage withdrawals');
        Permission::findOrCreate('view revenue reports');

        $admin = Role::findOrCreate('admin');

        $admin->givePermissionTo([
            'approve campaigns',
            'manage users',
            'review verifications',
            'manage withdrawals',
            'view revenue reports',
            'manage campaigns',
        ]);
    }
}
