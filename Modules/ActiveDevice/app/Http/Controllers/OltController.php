<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreOltRequest;
use Modules\ActiveDevice\Http\Requests\UpdateOltRequest;
use Modules\ActiveDevice\Models\Olt;
use Modules\ActiveDevice\Models\Router;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class OltController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $olts = Olt::with(['area', 'pop', 'servicePorts', 'interfaces', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'sourceConnections.sourceInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'destinationConnections.destinationInterface'])->latest()->paginate(10);

        return Inertia::render('ActiveDevice::Olt/Index', [
            'olts' => $olts,
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

        if ($request->hasFile('device_image')) {
            $data['device_image'] = $request->file('device_image')->store('olt-images', 'public');
        }

        $olt = Olt::create($data);

        if ($request->has('service_ports')) {
            foreach ($request->service_ports as $portData) {
                $olt->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

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
        $allDevices = collect();
        $allDevices = $allDevices->merge(Router::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AdSwitch::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(Olt::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Ont::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AccessPoint::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\Cpe\Models\Cpe::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));

        return Inertia::render('ActiveDevice::Olt/Show', [
            'olt' => $olt->load(['area', 'pop', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'servicePorts', 'interfaces']),
            'availableDevices' => $allDevices,
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
        $data = $request->validated();

        if ($request->hasFile('device_image')) {
            $data['device_image'] = $request->file('device_image')->store('olt-images', 'public');
        }

        $olt->update($data);

        if ($request->has('service_ports')) {
            $olt->servicePorts()->delete();
            foreach ($request->service_ports as $portData) {
                $olt->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

        return redirect()->route('active-device.olt.index')->with('success', 'OLT updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Olt $olt): RedirectResponse
    {
        $olt->delete();

        return redirect()->route('active-device.olt.index')->with('success', 'OLT deleted successfully.');
    }
}
