<?php

namespace Modules\User\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Company\Models\Company;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create Roles
    $this->superadminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'superadmin']);
    $this->companyAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'company_admin']);

    // Create a company
    $this->company = Company::create([
        'name' => 'Test Company',
        'code' => 'TEST',
        'email' => 'test@example.com',
        'phone' => '123456789',
        'address' => 'Test Address',
        'is_active' => true,
    ]);

    // Create users
    $this->superadmin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->superadmin->assignRole('superadmin');

    $this->companyAdmin = User::create([
        'name' => 'Company Admin',
        'email' => 'company@example.com',
        'password' => bcrypt('password'),
        'company_id' => $this->company->id,
    ]);
    $this->companyAdmin->assignRole('company_admin');
});

it('allows superadmin to access user index', function () {
    $this->actingAs($this->superadmin)
        ->get(route('user.index'))
        ->assertSuccessful();
});

it('forbids company admin from accessing user index', function () {
    $this->actingAs($this->companyAdmin)
        ->get(route('user.index'))
        ->assertForbidden();
});

it('allows superadmin to access company index', function () {
    $this->actingAs($this->superadmin)
        ->get(route('companies.index'))
        ->assertSuccessful();
});

it('forbids company admin from accessing company index', function () {
    $this->actingAs($this->companyAdmin)
        ->get(route('companies.index'))
        ->assertForbidden();
});

it('allows company admin to view their own company show page', function () {
    $this->actingAs($this->companyAdmin)
        ->get(route('companies.show', $this->company->id))
        ->assertSuccessful();
});

it('forbids company admin from viewing other company show page', function () {
    $otherCompany = Company::create([
        'name' => 'Other Company',
        'code' => 'OTHER',
        'email' => 'other@example.com',
        'phone' => '987654321',
        'address' => 'Other Address',
        'is_active' => true,
    ]);

    $this->actingAs($this->companyAdmin)
        ->get(route('companies.show', $otherCompany->id))
        ->assertNotFound();
});

it('forbids company admin from accessing company edit page', function () {
    $this->actingAs($this->companyAdmin)
        ->get(route('companies.edit', $this->company->id))
        ->assertForbidden();
});
