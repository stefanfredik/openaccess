<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ActiveDevice\Models\Olt;
use Modules\Area\Models\InfrastructureArea;
use Modules\Company\Models\Company;
use Modules\Pop\Models\Pop;

/**
 * @property \Modules\Company\Models\Company $company
 * @property \Modules\Area\Models\InfrastructureArea $area
 * @property \Modules\Pop\Models\Pop $pop
 * @property \App\Models\User $user
 */
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

it('can display olt index page', function () {
    Olt::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test OLT',
        'code' => 'OLT-001',
        'status' => 'Active',
    ]);

    $response = $this->get(route('active-device.olt.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('ActiveDevice::Olt/Index', false)
            ->has('olts')
    );
});

it('can display olt create page', function () {
    $response = $this->get(route('active-device.olt.create'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('ActiveDevice::Olt/Create', false)
            ->has('areas')
            ->has('pops')
    );
});

it('can create a new olt', function () {
    $data = [
        'name' => 'New OLT',
        'code' => 'OLT-002',
        'infrastructure_area_id' => $this->area->id,
        'pop_id' => $this->pop->id,
        'ip_address' => '192.168.1.10',
        'brand' => 'ZTE',
        'model' => 'C320',
        'status' => 'Active',
        'pon_type' => 'GPON',
    ];

    $response = $this->post(route('active-device.olt.store'), $data);

    $response->assertRedirect(route('active-device.olt.index'));
    $this->assertDatabaseHas('ad_olts', [
        'name' => 'New OLT',
        'code' => 'OLT-002',
    ]);
});

it('can display olt show page', function () {
    $olt = Olt::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test OLT',
        'code' => 'OLT-001',
        'status' => 'Active',
    ]);

    $response = $this->get(route('active-device.olt.show', $olt));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('ActiveDevice::Olt/Show', false)
            ->has('olt')
    );
});

it('can update an olt', function () {
    $olt = Olt::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test OLT',
        'code' => 'OLT-001',
        'status' => 'Active',
    ]);

    $data = [
        'name' => 'Updated OLT',
        'code' => 'OLT-001',
        'infrastructure_area_id' => $this->area->id,
        'status' => 'Inactive',
        'pon_type' => 'EPON',
    ];

    $response = $this->put(route('active-device.olt.update', $olt), $data);

    $response->assertRedirect(route('active-device.olt.index'));
    $this->assertDatabaseHas('ad_olts', [
        'id' => $olt->id,
        'name' => 'Updated OLT',
        'status' => 'Inactive',
    ]);
});

it('can delete an olt', function () {
    $olt = Olt::create([
        'company_id' => $this->company->id,
        'infrastructure_area_id' => $this->area->id,
        'name' => 'Test OLT',
        'code' => 'OLT-001',
        'status' => 'Active',
    ]);

    $response = $this->delete(route('active-device.olt.destroy', $olt));

    $response->assertRedirect(route('active-device.olt.index'));
    $this->assertDatabaseMissing('ad_olts', [
        'id' => $olt->id,
    ]);
});
