<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\Company\Models\Company;
use Modules\Area\Models\InfrastructureArea;
use Modules\Site\Models\Site;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');

        if ($isSuperAdmin) {
            return Inertia::render('Dashboard::Index', [
                'stats' => [
                    'companies' => Company::count(),
                    'users' => User::count(),
                ],
                'isSuperAdmin' => true
            ]);
        }

        return Inertia::render('Dashboard::Index', [
            'stats' => [
                'areas' => InfrastructureArea::count(),
                'sites' => Site::count(),
                'pops' => \Modules\Pop\Models\Pop::count(),
                'active_devices' => \Modules\ActiveDevice\Models\Olt::count() +
                    \Modules\ActiveDevice\Models\Ont::count() +
                    \Modules\ActiveDevice\Models\Router::count(),
            ],
            'isSuperAdmin' => false
        ]);
    }

    public function pendataan()
    {
        if (auth()->user()->hasRole('superadmin')) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Dashboard::Pendataan', [
            'stats' => [
                'areas' => InfrastructureArea::count(),
                'sites' => Site::count(),
                'pops' => \Modules\Pop\Models\Pop::count(),
                'active_devices' => \Modules\ActiveDevice\Models\Olt::count() +
                    \Modules\ActiveDevice\Models\Ont::count() +
                    \Modules\ActiveDevice\Models\Router::count(),
                'passive_devices' => \Modules\PassiveDevice\Models\Odp::count() +
                    \Modules\PassiveDevice\Models\Pole::count() +
                    \Modules\PassiveDevice\Models\Tower::count(),
            ]
        ]);
    }
}
