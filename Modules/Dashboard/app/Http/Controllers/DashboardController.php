<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\Company\Models\Company;
use Modules\Area\Models\InfrastructureArea;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->user()->hasRole('superadmin')) {
            return Inertia::render('Dashboard::Index', [
                'isSuperAdmin' => true,
                'stats' => [
                    'companies' => \Modules\Company\Models\Company::count(),
                    'users' => \App\Models\User::count(),
                ]
            ]);
        }

        return Inertia::render('Dashboard::Index', [
            'isSuperAdmin' => false,
            'stats' => [
                'areas' => InfrastructureArea::where('company_id', $request->user()->company_id)->count(),
                'pops' => \Modules\Pop\Models\Pop::where('company_id', $request->user()->company_id)->count(),
                'active_devices' => \Modules\ActiveDevice\Models\Olt::where('company_id', $request->user()->company_id)->count() +
                    \Modules\ActiveDevice\Models\Ont::where('company_id', $request->user()->company_id)->count() +
                    \Modules\ActiveDevice\Models\Router::where('company_id', $request->user()->company_id)->count(),
            ]
        ]);
    }

    public function pendataan(Request $request)
    {
        if ($request->user()->hasRole('superadmin')) {
            return redirect()->route('dashboard');
        }

        $companyId = $request->user()->company_id;

        return Inertia::render('Dashboard::Pendataan', [
            'stats' => [
                'areas' => InfrastructureArea::where('company_id', $companyId)->count(),
                'pops' => \Modules\Pop\Models\Pop::where('company_id', $companyId)->count(),
                'active_devices' => [
                    'total' => \Modules\ActiveDevice\Models\Olt::where('company_id', $companyId)->count() + 
                               \Modules\ActiveDevice\Models\Ont::where('company_id', $companyId)->count() + 
                               \Modules\ActiveDevice\Models\Router::where('company_id', $companyId)->count(),
                    'online' => \Modules\ActiveDevice\Models\Olt::where('company_id', $companyId)->where('is_active', 1)->count() + 
                                \Modules\ActiveDevice\Models\Ont::where('company_id', $companyId)->where('is_active', 1)->count() + 
                                \Modules\ActiveDevice\Models\Router::where('company_id', $companyId)->where('is_active', 1)->count(),
                    'olt' => \Modules\ActiveDevice\Models\Olt::where('company_id', $companyId)->count(),
                    'ont' => \Modules\ActiveDevice\Models\Ont::where('company_id', $companyId)->count(),
                    'router' => \Modules\ActiveDevice\Models\Router::where('company_id', $companyId)->count(),
                    'switch' => \Modules\ActiveDevice\Models\AdSwitch::where('company_id', $companyId)->count(),
                    'ap' => \Modules\ActiveDevice\Models\AccessPoint::where('company_id', $companyId)->count(),
                ],
                'passive_devices' => [
                    'total' => \Modules\PassiveDevice\Models\Odp::where('company_id', $companyId)->count() + 
                               \Modules\PassiveDevice\Models\Pole::where('company_id', $companyId)->count() + 
                               \Modules\PassiveDevice\Models\Tower::where('company_id', $companyId)->count(),
                    'odp' => \Modules\PassiveDevice\Models\Odp::where('company_id', $companyId)->count(),
                    'odf' => \Modules\PassiveDevice\Models\Odf::where('company_id', $companyId)->count(),
                    'pole' => \Modules\PassiveDevice\Models\Pole::where('company_id', $companyId)->count(),
                    'tower' => \Modules\PassiveDevice\Models\Tower::where('company_id', $companyId)->count(),
                    'joint_box' => \Modules\PassiveDevice\Models\JointBox::where('company_id', $companyId)->count(),
                ],
            ],
            'recent_activities' => [
                ['id' => 1, 'user' => 'System', 'action' => 'Penambahan ODP Baru', 'time' => '2 jam yang lalu', 'type' => 'passive'],
                ['id' => 2, 'user' => 'Admin ISP', 'action' => 'Update Lokasi POP', 'time' => '5 jam yang lalu', 'type' => 'infrastructure'],
                ['id' => 3, 'user' => 'Technician', 'action' => 'Koneksi OLT Baru', 'time' => '1 hari yang lalu', 'type' => 'active'],
            ]
        ]);
    }
}
