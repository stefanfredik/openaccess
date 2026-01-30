<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreAccessPointRequest;
use Modules\ActiveDevice\Http\Requests\UpdateAccessPointRequest;
use Modules\ActiveDevice\Models\AccessPoint;
use Modules\ActiveDevice\Services\AccessPointService;
use Modules\ActiveDevice\Services\DeviceService;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class AccessPointController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private readonly DeviceService $deviceService,
        private readonly AccessPointService $accessPointService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $accessPoints = AccessPoint::query()
            ->with([
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

        return Inertia::render('ActiveDevice::AccessPoint/Index', [
            'accessPoints' => $accessPoints,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ActiveDevice::AccessPoint/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'pops' => Pop::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccessPointRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        $this->accessPointService->create(
            $data,
            null,
            $request->service_ports
        );

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', $this->flashCreated('access_point'));
        }

        return redirect()->route('active-device.access-point.index')->with('success', $this->flashCreated('access_point'));
    }

    /**
     * Show the specified resource.
     */
    public function show(AccessPoint $accessPoint): Response
    {
        return Inertia::render('ActiveDevice::AccessPoint/Show', [
            'accessPoint' => $accessPoint->load(['area', 'pop', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'servicePorts', 'interfaces']),
            'availableDevices' => $this->deviceService->getAllDevices(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccessPoint $accessPoint): Response
    {
        return Inertia::render('ActiveDevice::AccessPoint/Edit', [
            'accessPoint' => $accessPoint->load('servicePorts'),
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'pops' => Pop::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccessPointRequest $request, AccessPoint $accessPoint): RedirectResponse
    {
        $this->accessPointService->update(
            $accessPoint,
            $request->validated(),
            null,
            $request->has('service_ports') ? $request->service_ports : null,
            $request->user()->company_id
        );

        return redirect()->route('active-device.access-point.index')->with('success', $this->flashUpdated('access_point'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccessPoint $accessPoint): RedirectResponse
    {
        $this->accessPointService->delete($accessPoint);

        return redirect()->route('active-device.access-point.index')->with('success', $this->flashDeleted('access_point'));
    }
}
