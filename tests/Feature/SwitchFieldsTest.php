<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\ActiveDevice\Models\AdSwitch;
use Modules\Area\Models\InfrastructureArea;
use Modules\Company\Models\Company;
use Modules\Pop\Models\Pop;

uses(RefreshDatabase::class);

test('can create switch with new fields and photo', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'company_id' => Company::create([
            'name' => 'Test Company',
            'code' => 'TC01',
            'address' => '123 Test St',
            'phone' => '1234567890',
            'email' => 'test@company.com',
        ])->id,
    ]);

    $area = InfrastructureArea::create([
        'company_id' => $user->company_id,
        'name' => 'Test Area',
        'code' => 'TA01',
    ]);

    $pop = Pop::create([
        'company_id' => $user->company_id,
        'infrastructure_area_id' => $area->id,
        'area_id' => $area->id,
        'name' => 'Test Pop',
        'code' => 'POP02',
        'address' => 'Test Address',
        'latitude' => '-6.200000',
        'longitude' => '106.816666',
    ]);

    $photo = UploadedFile::fake()->image('switch.jpg');

    $response = $this->actingAs($user)->post(route('active-device.switch.store'), [
        'infrastructure_area_id' => $area->id,
        'pop_id' => $pop->id,
        'code' => 'SW-001',
        'name' => 'Test Switch',
        'switch_type' => 'Access',
        'port_count' => 24,
        'is_active' => true,
        'username' => 'admin',
        'password' => 'secret',
        'purchase_year' => 2023,
        'photo' => $photo,
        'service_ports' => [
            ['name' => 'SSH', 'port' => 22, 'status' => 'Active'],
        ],
    ]);

    $response->assertRedirect(route('active-device.switch.index'));

    $switch = AdSwitch::first();

    expect($switch->username)->toBe('admin');
    expect($switch->purchase_year)->toBe(2023);

    // Check if photo is stored
    expect($switch->photo)->not->toBeNull();
    Storage::disk('public')->assertExists($switch->photo);

    // Verify password is not in array/JSON serialization
    $array = $switch->toArray();
    expect(array_key_exists('password', $array))->toBeFalse();
});

test('unmanageable switch stores null technical fields', function () {
    $user = User::factory()->create([
        'company_id' => Company::create([
            'name' => 'Test Company',
            'code' => 'TC02',
            'address' => '123 Test St',
            'phone' => '1234567890',
            'email' => 'test@company.com',
        ])->id,
    ]);

    $area = InfrastructureArea::create([
        'company_id' => $user->company_id,
        'name' => 'Test Area',
        'code' => 'TA01',
    ]);

    $pop = Pop::create([
        'company_id' => $user->company_id,
        'infrastructure_area_id' => $area->id,
        'area_id' => $area->id,
        'name' => 'Test Pop',
        'code' => 'POP02',
        'address' => 'Test Address',
        'latitude' => '-6.200000',
        'longitude' => '106.816666',
    ]);

    $response = $this->actingAs($user)->post(route('active-device.switch.store'), [
        'infrastructure_area_id' => $area->id,
        'pop_id' => $pop->id,
        'code' => 'SW-Unmanaged',
        'name' => 'Unmanaged Switch',
        'switch_type' => 'Unmanageable',
        'port_count' => 8,
        'is_active' => true,
        // Fields omitted or empty
        'username' => '',
        'password' => '',
        'ip_address' => '',
        'purchase_year' => 2024,
    ]);

    $response->assertRedirect(route('active-device.switch.index'));

    $switch = AdSwitch::where('code', 'SW-Unmanaged')->first();

    expect($switch->switch_type)->toBe('Unmanageable');
    expect($switch->username)->toBeNull();
    expect($switch->password)->toBeNull();
    expect($switch->ip_address)->toBeNull();
    expect($switch->purchase_year)->toBe(2024);
});
