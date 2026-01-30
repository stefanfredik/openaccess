<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreOltRequest;
use Modules\ActiveDevice\Http\Requests\UpdateOltRequest;
use Modules\ActiveDevice\Models\Olt;
use Modules\ActiveDevice\Services\DeviceService;
use Modules\ActiveDevice\Services\OltService;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class OltController extends Controller
{
    public function __construct(
        private readonly DeviceService $deviceService,
        private readonly OltService $oltService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $olts = Olt::query()
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

        return Inertia::render('ActiveDevice::Olt/Index', [
            'olts' => $olts,
            'areas' => InfrastructureArea::all(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ActiveDevice::Olt/Create', [
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOltRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        $this->oltService->create(
            $data,
            $request->file('device_image'),
            $request->service_ports
        );

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'OLT created successfully.');
        }

        return redirect()->route('active-device.olt.index')->with('success', 'OLT created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Olt $olt): Response
    {
        return Inertia::render('ActiveDevice::Olt/Show', [
            'olt' => $olt->load(['area', 'pop', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'servicePorts', 'interfaces']),
            'availableDevices' => $this->deviceService->getAllDevices(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Olt $olt): Response
    {
        return Inertia::render('ActiveDevice::Olt/Edit', [
            'olt' => $olt->load(['servicePorts', 'interfaces']),
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOltRequest $request, Olt $olt): RedirectResponse
    {
        $this->oltService->update(
            $olt,
            $request->validated(),
            $request->file('device_image'),
            $request->has('service_ports') ? $request->service_ports : null,
            $request->user()->company_id
        );

        return redirect()->route('active-device.olt.index')->with('success', 'OLT updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Olt $olt): RedirectResponse
    {
        $this->oltService->delete($olt);

        return redirect()->route('active-device.olt.index')->with('success', 'OLT deleted successfully.');
    }
}
