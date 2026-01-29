<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreAccessPointRequest;
use Modules\ActiveDevice\Http\Requests\UpdateAccessPointRequest;
use Modules\ActiveDevice\Models\AccessPoint;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class AccessPointController extends Controller
{
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
            'areas' => InfrastructureArea::all(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ActiveDevice::AccessPoint/Create', [
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccessPointRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;
        $accessPoint = AccessPoint::create($data);

        if ($request->has('service_ports')) {
            foreach ($request->service_ports as $portData) {
                $accessPoint->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'Access Point created successfully.');
        }

        return redirect()->route('active-device.access-point.index')->with('success', 'Access Point created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(AccessPoint $accessPoint): Response
    {
        $allDevices = collect();
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Router::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AdSwitch::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Olt::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Ont::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(AccessPoint::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\Cpe\Models\Cpe::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));

        return Inertia::render('ActiveDevice::AccessPoint/Show', [
            'accessPoint' => $accessPoint->load(['area', 'pop', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'servicePorts', 'interfaces']),
            'availableDevices' => $allDevices,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccessPoint $accessPoint): Response
    {
        return Inertia::render('ActiveDevice::AccessPoint/Edit', [
            'accessPoint' => $accessPoint->load('servicePorts'),
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccessPointRequest $request, AccessPoint $accessPoint): RedirectResponse
    {
        $accessPoint->update($request->validated());

        if ($request->has('service_ports')) {
            $accessPoint->servicePorts()->delete();
            foreach ($request->service_ports as $portData) {
                $accessPoint->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

        return redirect()->route('active-device.access-point.index')->with('success', 'Access Point updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccessPoint $accessPoint): RedirectResponse
    {
        $accessPoint->delete();

        return redirect()->route('active-device.access-point.index')->with('success', 'Access Point deleted successfully.');
    }
}
