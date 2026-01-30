<?php

namespace Modules\Area\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Area\Models\InfrastructureArea;
use Modules\Company\Models\Company;
use Tests\TestCase;

class AreaShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_show_returns_correct_data_and_stats()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'code' => 'TEST',
            'address' => 'Test Address',
            'phone' => '123456789',
            'email' => 'test@example.com',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $this->actingAs($user);

        $area = InfrastructureArea::create([
            'company_id' => $company->id,
            'name' => 'Test Area',
            'type' => 'area',
            'boundary' => [[-6.1754, 106.8272], [-6.1754, 106.8273], [-6.1755, 106.8273], [-6.1755, 106.8272]],
        ]);

        $response = $this->get(route('area.show', $area->id));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('area')
            ->has('stats', fn (Assert $page) => $page
                ->has('pops_count')
                ->has('servers_count')
                ->has('olts_count')
                ->has('odps_count')
                ->has('cpes_count')
                ->etc()
            )
        );
    }
}
