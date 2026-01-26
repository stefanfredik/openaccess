<?php

namespace Modules\ActiveDevice\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\Company\Models\Company;
use Modules\ActiveDevice\Models\Router;
use Modules\ActiveDevice\Models\AdSwitch;
use Modules\ActiveDevice\Models\DeviceConnection;
use Modules\Area\Models\InfrastructureArea;

class DeviceConnectionTest extends TestCase
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
            'address' => 'Test Address',
            'phone' => '08123',
            'email' => 'test@test.com'
        ]);

        $this->area = InfrastructureArea::create([
            'company_id' => $this->company->id,
            'name' => 'Test Area',
            'code' => 'AREA-01',
            'type' => 'Village'
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_create_connection_between_devices()
    {
        $router = Router::create([
            'company_id' => $this->company->id,
            'infrastructure_area_id' => $this->area->id,
            'code' => 'R-01',
            'name' => 'Core Router',
        ]);

        $switch = AdSwitch::create([
            'company_id' => $this->company->id,
            'infrastructure_area_id' => $this->area->id,
            'code' => 'S-01',
            'name' => 'Access Switch',
        ]);

        $data = [
            'source_id' => $router->id,
            'source_type' => get_class($router),
            'destination_id' => $switch->id,
            'destination_type' => get_class($switch),
            'connection_type' => 'Downlink',
            'port_name' => 'ETH 0/1',
        ];

        $response = $this->post('/active-devices/connections', $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('ad_device_connections', [
            'source_id' => $router->id,
            'destination_id' => $switch->id,
            'connection_type' => 'Downlink',
        ]);
    }

    public function test_can_view_topology()
    {
        $router = Router::create([
            'company_id' => $this->company->id,
            'infrastructure_area_id' => $this->area->id,
            'code' => 'R-01',
            'name' => 'Core Router',
        ]);

        $response = $this->get(route('active-device.topology'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('ActiveDevice::Topology', false)
                ->has('topology')
        );
    }
}
