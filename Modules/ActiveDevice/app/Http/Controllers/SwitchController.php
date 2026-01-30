<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreSwitchRequest;
use Modules\ActiveDevice\Http\Requests\UpdateSwitchRequest;
use Modules\ActiveDevice\Models\AdSwitch;
use Modules\ActiveDevice\Services\DeviceService;
use Modules\ActiveDevice\Services\SwitchService;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class SwitchController extends Controller
{
    public function __construct(
        private readonly DeviceService $deviceService,
        private readonly SwitchService $switchService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $switches = AdSwitch::query()
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

        return Inertia::render('ActiveDevice::Switch/Index', [
            'switches' => $switches,
            'areas' => InfrastructureArea::all(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ActiveDevice::Switch/Create', [
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSwitchRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        $this->switchService->create(
            $data,
            $request->file('photo'),
            $request->service_ports
        );

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'Switch created successfully.');
        }

        return redirect()->route('active-device.switch.index')->with('success', 'Switch created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(AdSwitch $switch): Response
    {
        return Inertia::render('ActiveDevice::Switch/Show', [
            'networkSwitch' => $switch->load(['area', 'pop', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'servicePorts', 'interfaces']),
            'availableDevices' => $this->deviceService->getAllDevices(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdSwitch $switch): Response
    {
        return Inertia::render('ActiveDevice::Switch/Edit', [
            'networkSwitch' => $switch->load('servicePorts'),
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSwitchRequest $request, AdSwitch $switch): RedirectResponse
    {
        $this->switchService->update(
            $switch,
            $request->validated(),
            $request->file('photo'),
            $request->has('service_ports') ? $request->service_ports : null,
            $request->user()->company_id
        );

        return redirect()->route('active-device.switch.index')->with('success', 'Switch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdSwitch $switch): RedirectResponse
    {
        $this->switchService->delete($switch);

        return redirect()->route('active-device.switch.index')->with('success', 'Switch deleted successfully.');
    }
}
