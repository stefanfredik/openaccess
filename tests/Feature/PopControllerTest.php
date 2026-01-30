<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    $this->user = User::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $this->actingAs($this->user);
});

it('can display pop index page', function () {
    Pop::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'area_id' => $this->area->id,
        'name' => 'Test POP',
        'code' => 'POP-001',
        'status' => 'Active',
    ]);

    $response = $this->get(route('pop.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Pop::Index', false)
            ->has('pops')
    );
});

it('can display pop create page', function () {
    $response = $this->get(route('pop.create'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Pop::Create', false)
            ->has('areas')
    );
});

it('can create a new pop', function () {
    $data = [
        'name' => 'New POP',
        'code' => 'POP-002',
        'infrastructure_area_id' => $this->area->id,
        'area_id' => $this->area->id,
        'status' => 'Active',
    ];

    $response = $this->post(route('pop.store'), $data);

    $response->assertRedirect(route('pop.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('pops', [
        'name' => 'New POP',
        'code' => 'POP-002',
    ]);
});

it('can display pop show page', function () {
    $pop = Pop::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'area_id' => $this->area->id,
        'name' => 'Test POP',
        'code' => 'POP-001',
        'status' => 'Active',
    ]);

    $response = $this->get(route('pop.show', $pop));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Pop::Show', false)
            ->has('pop')
    );
});

it('can update a pop', function () {
    $pop = Pop::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'area_id' => $this->area->id,
        'name' => 'Test POP',
        'code' => 'POP-001',
        'status' => 'Active',
    ]);

    $data = [
        'name' => 'Updated POP',
        'code' => 'POP-001',
        'infrastructure_area_id' => $this->area->id,
        'area_id' => $this->area->id,
        'status' => 'Active',
    ];

    $response = $this->put(route('pop.update', $pop), $data);

    $response->assertRedirect(route('pop.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('pops', [
        'id' => $pop->id,
        'name' => 'Updated POP',
    ]);
});

it('can delete a pop', function () {
    $pop = Pop::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'area_id' => $this->area->id,
        'name' => 'Test POP',
        'code' => 'POP-001',
        'status' => 'Active',
    ]);

    $response = $this->delete(route('pop.destroy', $pop));

    $response->assertRedirect(route('pop.index'));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('pops', [
        'id' => $pop->id,
    ]);
});

it('validates required fields when creating pop', function () {
    $response = $this->post(route('pop.store'), []);

    $response->assertSessionHasErrors(['name', 'code']);
});
