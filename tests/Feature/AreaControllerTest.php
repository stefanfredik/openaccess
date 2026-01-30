<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Area\Models\InfrastructureArea;
use Modules\Company\Models\Company;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::create([
        'name' => 'Test Company',
        'code' => 'TEST',
        'address' => 'Test Address',
        'phone' => '08123',
        'email' => 'test@company.com',
    ]);

    $this->user = User::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $this->actingAs($this->user);
});

it('can display area index page', function () {
    InfrastructureArea::create([
        'company_id' => $this->company->id,
        'name' => 'Test Area',
        'code' => 'AREA-001',
        'type' => 'region',
    ]);

    $response = $this->get(route('area.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Area::Index', false)
            ->has('areas')
    );
});

it('can display area create page', function () {
    $response = $this->get(route('area.create'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Area::Create', false)
    );
});

it('can create a new area', function () {
    $data = [
        'name' => 'New Area',
        'code' => 'AREA-002',
        'type' => 'region',
    ];

    $response = $this->post(route('area.store'), $data);

    $response->assertRedirect(route('area.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('infrastructure_areas', [
        'name' => 'New Area',
        'code' => 'AREA-002',
    ]);
});

it('can display area edit page', function () {
    $area = InfrastructureArea::create([
        'company_id' => $this->company->id,
        'name' => 'Test Area',
        'code' => 'AREA-001',
        'type' => 'region',
    ]);

    $response = $this->get(route('area.edit', $area));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Area::Edit', false)
            ->has('area')
    );
});

it('can update an area', function () {
    $area = InfrastructureArea::create([
        'company_id' => $this->company->id,
        'name' => 'Test Area',
        'code' => 'AREA-001',
        'type' => 'region',
    ]);

    $data = [
        'name' => 'Updated Area',
        'code' => 'AREA-001',
        'type' => 'region',
    ];

    $response = $this->put(route('area.update', $area), $data);

    $response->assertRedirect(route('area.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('infrastructure_areas', [
        'id' => $area->id,
        'name' => 'Updated Area',
    ]);
});

it('can delete an area without dependencies', function () {
    $area = InfrastructureArea::create([
        'company_id' => $this->company->id,
        'name' => 'Test Area',
        'code' => 'AREA-001',
        'type' => 'region',
    ]);

    $response = $this->delete(route('area.destroy', $area));

    $response->assertRedirect(route('area.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('infrastructure_areas', [
        'id' => $area->id,
    ]);
});

it('validates required fields when creating area', function () {
    $response = $this->post(route('area.store'), []);

    $response->assertSessionHasErrors(['name', 'type']);
});
