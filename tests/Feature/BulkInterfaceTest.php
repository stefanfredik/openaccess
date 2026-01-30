<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ActiveDevice\Models\AdSwitch;
use Modules\ActiveDevice\Models\DeviceInterface;
use Modules\Area\Models\InfrastructureArea;
use Modules\Company\Models\Company;
use Modules\Pop\Models\Pop;
use Tests\TestCase;

class BulkInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_single_interface()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'address' => 'Test Address',
            'code' => 'TC01',
            'phone' => '08123456789',
            'email' => 'test@company.com',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $area = InfrastructureArea::create([
            'name' => 'Test Area',
            'code' => 'TA01',
            'company_id' => $company->id,
        ]);

        $pop = Pop::create([
            'company_id' => $company->id,
            'infrastructure_area_id' => $area->id,
            'area_id' => $area->id,
            'name' => 'Test Pop',
            'code' => 'POP01',
            'address' => 'Test Address',
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
        ]);

        $switch = AdSwitch::create([
            'company_id' => $company->id,
            'pop_id' => $pop->id,
            'infrastructure_area_id' => $area->id,
            'area_id' => $area->id,
            'name' => 'Test Switch',
            'code' => 'SW01',
            'brand' => 'Brand',
            'model' => 'Model',
            'switch_type' => 'Manageable (Access)',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('active-device.interfaces.store'), [
            'interfacable_id' => $switch->id,
            'interfacable_type' => get_class($switch),
            'name' => 'GE0/1',
            'status' => 'down',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ad_device_interfaces', [
            'interfacable_id' => $switch->id,
            'interfacable_type' => get_class($switch),
            'name' => 'GE0/1',
        ]);
    }

    public function test_can_create_bulk_interfaces()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'address' => 'Test Address',
            'code' => 'TC02',
            'phone' => '08123456789',
            'email' => 'test2@company.com',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $area = InfrastructureArea::create([
            'name' => 'Test Area',
            'code' => 'TA02',
            'company_id' => $company->id,
        ]);

        $pop = Pop::create([
            'company_id' => $company->id,
            'infrastructure_area_id' => $area->id,
            'area_id' => $area->id,
            'name' => 'Test Pop',
            'code' => 'POP02',
            'address' => 'Test Address',
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
        ]);

        $switch = AdSwitch::create([
            'company_id' => $company->id,
            'pop_id' => $pop->id,
            'infrastructure_area_id' => $area->id,
            'area_id' => $area->id,
            'name' => 'Test Switch 2',
            'code' => 'SW02',
            'brand' => 'Brand',
            'model' => 'Model',
            'switch_type' => 'Manageable (Access)',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post(route('active-device.interfaces.store'), [
            'interfacable_id' => $switch->id,
            'interfacable_type' => get_class($switch),
            'status' => 'down',
            'is_bulk' => true,
            'prefix' => 'GE1/0/',
            'start_number' => 1,
            'count' => 5,
        ]);

        $response->assertSessionHasNoErrors();

        // Verify 5 interfaces created
        $this->assertEquals(5, DeviceInterface::where('interfacable_id', $switch->id)->count());

        // Verify specific names
        $this->assertDatabaseHas('ad_device_interfaces', [
            'name' => 'GE1/0/1',
            'interfacable_id' => $switch->id,
        ]);
        $this->assertDatabaseHas('ad_device_interfaces', [
            'name' => 'GE1/0/5',
            'interfacable_id' => $switch->id,
        ]);
    }
}
