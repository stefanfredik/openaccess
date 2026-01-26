<?php

namespace Modules\Area\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Area\Models\InfrastructureArea;
use Tests\TestCase;
use App\Models\User;
use Modules\Company\Models\Company;

class AreaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
    }

    public function test_can_list_areas()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'code' => 'TEST',
            'address' => 'Test Address',
            'phone' => '123456789',
            'email' => 'test@example.com',
        ]);
        
        $user = User::factory()->create([
            'company_id' => $company->id
        ]);
        
        $this->actingAs($user);
        
        $area = InfrastructureArea::create([
            'company_id' => $company->id,
            'name' => 'Test Area',
            'type' => 'area'
        ]);

        $response = $this->get(route('area.index'));

        $response->assertStatus(200);
    }
}
