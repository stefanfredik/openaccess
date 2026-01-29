<?php

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

use App\Models\User;
use Modules\ActiveDevice\Models\Router;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can create a router with the new fields', function () {
    Storage::fake('public');
    
    $company = \Modules\Company\Models\Company::create([
        'name' => 'Test Company',
        'code' => 'TC-001',
        'address' => 'Test Address',
        'phone' => '1234567890',
        'email' => 'test@company.com',
    ]);
    
    $user = User::factory()->create(['company_id' => $company->id]);
    
    $area = InfrastructureArea::create([
        'company_id' => $company->id,
        'name' => 'Test Area',
        'code' => 'TA-001',
    ]);
    
    $pop = Pop::create([
        'company_id' => $company->id,
        'infrastructure_area_id' => $area->id,
        'area_id' => $area->id, // Added this field
        'name' => 'Test POP',
        'code' => 'TP-001',
    ]);

    $data = [
        'infrastructure_area_id' => $area->id,
        'pop_id' => $pop->id,
        'code' => 'RTR-TEST-001',
        'name' => 'Test Router',
        'port_count' => 24,
        'username' => 'testuser',
        'password' => 'testpassword',
        'purchase_year' => 2025,
        'photo' => UploadedFile::fake()->image('router.jpg'),
    ];

    $response = $this->actingAs($user)
        ->post(route('active-device.router.store'), $data);

    $response->assertRedirect();
    
    $router = Router::where('code', 'RTR-TEST-001')->first();
    expect($router)->not->toBeNull();
    expect($router->username)->toBe('testuser');
    expect($router->purchase_year)->toBe(2025);
    expect($router->photo)->not->toBeNull();
    
    // Ensure photo is stored
    Storage::disk('public')->assertExists($router->photo);
});
