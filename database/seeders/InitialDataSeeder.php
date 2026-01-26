<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $superadminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'superadmin']);
        $companyAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'company_admin']);

        // Create Superadmin
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@openaccess.id'],
            [
                'name' => 'Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_active' => true,
            ]
        );

        if (!$user->hasRole('superadmin')) {
            $user->assignRole($superadminRole);
        }
    }
}
