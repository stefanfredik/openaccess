<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ActiveDevice\Models\Router;
use Modules\Area\Models\InfrastructureArea;
use Modules\Company\Models\Company;
use Modules\Pop\Models\Pop;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::create([
        'name' => 'Test Company',
        'code' => 'TEST',
        'address' => 'Test Address',
        'phone' => '08123',
        'email' => 'test@company.com',
    ]);

    $this->area = InfrastructureArea::create([
        'company_id' => $this->company->id,
        'name' => 'Test Area',
        'code' => 'AREA-01',
        'type' => 'Village',
    ]);

    $this->pop = Pop::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'area_id' => $this->area->id,
        'name' => 'Test POP',
        'code' => 'POP-01',
        'status' => 'Active',
    ]);

    $this->user = User::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $this->actingAs($this->user);
});

it('can display router index page', function () {
    Router::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test Router',
        'code' => 'RTR-001',
    ]);

    $response = $this->get(route('active-device.router.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('ActiveDevice::Router/Index', false)
            ->has('routers')
    );
});

it('can display router create page', function () {
    $response = $this->get(route('active-device.router.create'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('ActiveDevice::Router/Create', false)
            ->has('areas')
            ->has('pops')
    );
});

it('can create a new router', function () {
    $data = [
        'name' => 'New Router',
        'code' => 'RTR-002',
        'infrastructure_area_id' => $this->area->id,
        'pop_id' => $this->pop->id,
        'ip_address' => '192.168.1.1',
        'brand' => 'Mikrotik',
        'model' => 'CCR1036',
        'port_count' => 24,
        'status' => 'Active',
    ];

    $response = $this->post(route('active-device.router.store'), $data);

    $response->assertRedirect(route('active-device.router.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('ad_routers', [
        'name' => 'New Router',
        'code' => 'RTR-002',
    ]);
});

it('can display router show page', function () {
    $router = Router::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test Router',
        'code' => 'RTR-001',
    ]);

    $response = $this->get(route('active-device.router.show', $router));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('ActiveDevice::Router/Show', false)
            ->has('router')
    );
});

it('can display router edit page', function () {
    $router = Router::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test Router',
        'code' => 'RTR-001',
    ]);

    $response = $this->get(route('active-device.router.edit', $router));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('ActiveDevice::Router/Edit', false)
            ->has('router')
    );
});

it('can update a router', function () {
    $router = Router::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test Router',
        'code' => 'RTR-001',
    ]);

    $data = [
        'name' => 'Updated Router',
        'code' => 'RTR-001',
        'infrastructure_area_id' => $this->area->id,
        'status' => 'Active',
        'port_count' => 24,
    ];

    $response = $this->put(route('active-device.router.update', $router), $data);

    $response->assertRedirect(route('active-device.router.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('ad_routers', [
        'id' => $router->id,
        'name' => 'Updated Router',
    ]);
});

it('can delete a router', function () {
    $router = Router::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test Router',
        'code' => 'RTR-001',
    ]);

    $response = $this->delete(route('active-device.router.destroy', $router));

    $response->assertRedirect(route('active-device.router.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('ad_routers', [
        'id' => $router->id,
    ]);
});

it('validates required fields when creating router', function () {
    $response = $this->post(route('active-device.router.store'), []);

    $response->assertSessionHasErrors(['name', 'code']);
});
