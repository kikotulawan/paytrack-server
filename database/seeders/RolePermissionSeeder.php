<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Permission, Role};
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions
        $permissions = [
            ['name' => 'view activity logs', 'details' => 'Can view activity logs'],
            ['name' => 'manage nature of collections', 'details' => 'Can create, update, delete nature of collections'],
            ['name' => 'manage accounts', 'details' => 'Can create, update, delete user accounts'],
            ['name' => 'manage trfs', 'details' => 'Can create, update, delete Trust Receipt Funds'],
            ['name' => 'manage tls', 'details' => 'Can create, update, delete Trust Liabilities'],
            ['name' => 'manage gfs', 'details' => 'Can create, update, delete General Funds'],
            ['name' => 'manage reports', 'details' => 'Can generate reports'],
        ];

        // Create permissions if they don't already exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], ['details' => $permission['details']]);
        }

        // Define roles
        $superadmin_role = Role::firstOrCreate(['name' => 'superadmin']);
        $staff_role = Role::firstOrCreate(['name' => 'staff']);
        $cashier_role = Role::firstOrCreate(['name' => 'cashier']);

        // Assign permissions to roles
        $superadmin_role->givePermissionTo(Permission::all());
        $staff_role->givePermissionTo(['manage trfs', 'manage tls', 'manage gfs', 'manage reports']);
        $cashier_role->givePermissionTo(['manage trfs', 'manage tls', 'manage gfs']);

        // Assign roles to users
        $superadmin = User::find(1);
        if ($superadmin) {
            $superadmin->assignRole('superadmin');
        }

        $staff = User::find(2);
        if ($staff) {
            $staff->assignRole('staff');
        }

        $cashier = User::find(3);
        if ($cashier) {
            $cashier->assignRole('cashier');
        }
    }
}