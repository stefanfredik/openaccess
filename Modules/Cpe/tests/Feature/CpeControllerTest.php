<?php

namespace Modules\Cpe\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\Company\Models\Company;
use Modules\Area\Models\InfrastructureArea;
use Modules\Cpe\Models\Cpe;

class CpeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Company',
            'code' => 'TEST',
            'address' => 'Test Company Address',
            'phone' => '08123456789',
            'email' => 'company@test.com',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->area = InfrastructureArea::create([
            'company_id' => $this->company->id,
            'name' => 'Test Area',
            'code' => 'AREA-01',
            'type' => 'Village',
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_list_cpes()
    {
        Cpe::create([
            'company_id' => $this->company->id,
            'infrastructure_area_id' => $this->area->id,
            'code' => 'CPE-01',
            'name' => 'Customer A',
            'address' => 'Address A',
            'type' => 'ONT',
        ]);

        $response = $this->get(route('cpe.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Cpe::Index', false)
                ->has('cpes.data', 1)
        );
    }

    public function test_can_create_cpe()
    {
        $data = [
            'infrastructure_area_id' => $this->area->id,
            'code' => 'CPE-NEW',
            'name' => 'New Customer',
            'address' => 'New Address',
            'type' => 'Router',
            'status' => 'Active',
        ];

        $response = $this->post(route('cpe.store'), $data);

        $response->assertRedirect(route('cpe.index'));
        $this->assertDatabaseHas('cpes', [
            'code' => 'CPE-NEW',
            'name' => 'New Customer',
        ]);
    }

    public function test_can_update_cpe()
    {
        $cpe = Cpe::create([
            'company_id' => $this->company->id,
            'infrastructure_area_id' => $this->area->id,
            'code' => 'CPE-OLD',
            'name' => 'Old Name',
            'address' => 'Old Address',
            'type' => 'ONT',
            'status' => 'Active',
        ]);

        $data = [
            'infrastructure_area_id' => $this->area->id,
            'code' => 'CPE-OLD',
            'name' => 'Updated Name',
            'address' => 'Updated Address',
            'type' => 'Router',
            'status' => 'Inactive',
        ];

        $response = $this->put(route('cpe.update', $cpe), $data);

        $response->assertRedirect(route('cpe.index'));
        $this->assertDatabaseHas('cpes', [
            'id' => $cpe->id,
            'name' => 'Updated Name',
            'status' => 'Inactive',
        ]);
    }

    public function test_can_delete_cpe()
    {
        $cpe = Cpe::create([
            'company_id' => $this->company->id,
            'infrastructure_area_id' => $this->area->id,
            'code' => 'CPE-DEL',
            'name' => 'Delete Me',
            'address' => 'Address',
            'type' => 'ONT',
        ]);

        $response = $this->delete(route('cpe.destroy', $cpe));

        $response->assertRedirect(route('cpe.index'));
        $this->assertDatabaseMissing('cpes', ['id' => $cpe->id]);
    }
}
