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
        return Inertia::render('Dashboard::Index', [
            'stats' => [
                'companies' => Company::count(),
                'areas' => InfrastructureArea::count(),
                'sites' => Site::count(),
                'users' => User::count(),
            ]
        ]);
    }
}
