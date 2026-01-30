<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreRouterRequest;
use Modules\ActiveDevice\Http\Requests\UpdateRouterRequest;
use Modules\ActiveDevice\Models\Router;
use Modules\ActiveDevice\Services\DeviceService;
use Modules\ActiveDevice\Services\RouterService;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class RouterController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private readonly DeviceService $deviceService,
        private readonly RouterService $routerService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $routers = Router::with([
            'area',
            'pop',
            'servicePorts',
            'interfaces',
            'sourceConnections.destination',
            'sourceConnections.destinationInterface',
            'sourceConnections.sourceInterface',
            'destinationConnections.source',
            'destinationConnections.sourceInterface',
            'destinationConnections.destinationInterface',
        ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->area_id && $request->area_id !== 'all', function ($query, $areaId) {
                $query->where('infrastructure_area_id', $areaId);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('ActiveDevice::Router/Index', [
            'routers' => $routers,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ActiveDevice::Router/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'pops' => Pop::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRouterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        $this->routerService->create(
            $data,
            $request->file('photo'),
            $request->service_ports
        );

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', $this->flashCreated('router'));
        }

        return redirect()->route('active-device.router.index')->with('success', $this->flashCreated('router'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Router $router): Response
    {
        return Inertia::render('ActiveDevice::Router/Show', [
            'router' => $router->load(['area', 'pop', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'servicePorts', 'interfaces']),
            'availableDevices' => $this->deviceService->getAllDevices(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Router $router): Response
    {
        return Inertia::render('ActiveDevice::Router/Edit', [
            'router' => $router->load('servicePorts'),
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'pops' => Pop::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRouterRequest $request, Router $router): RedirectResponse
    {
        $this->routerService->update(
            $router,
            $request->validated(),
            $request->file('photo'),
            $request->has('service_ports') ? $request->service_ports : null,
            $request->user()->company_id
        );

        return redirect()->route('active-device.router.index')->with('success', $this->flashUpdated('router'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Router $router): RedirectResponse
    {
        $this->routerService->delete($router);

        return redirect()->route('active-device.router.index')->with('success', $this->flashDeleted('router'));
    }
}
