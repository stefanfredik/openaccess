<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreSwitchRequest;
use Modules\ActiveDevice\Http\Requests\UpdateSwitchRequest;
use Modules\ActiveDevice\Models\AdSwitch;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class SwitchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $switches = AdSwitch::with(['area', 'pop'])->latest()->paginate(10);

        return Inertia::render('ActiveDevice::Switch/Index', [
            'switches' => $switches,
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
        $switch = AdSwitch::create($data);

        if ($request->has('service_ports')) {
            foreach ($request->service_ports as $portData) {
                $switch->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

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
        $allDevices = collect();
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Router::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(AdSwitch::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Olt::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Ont::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AccessPoint::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\Cpe\Models\Cpe::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));

        return Inertia::render('ActiveDevice::Switch/Show', [
            'networkSwitch' => $switch->load(['area', 'pop', 'sourceConnections.destination', 'destinationConnections.source', 'servicePorts', 'interfaces']),
            'availableDevices' => $allDevices,
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
        $switch->update($request->validated());

        if ($request->has('service_ports')) {
            $switch->servicePorts()->delete();
            foreach ($request->service_ports as $portData) {
                $switch->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

        return redirect()->route('active-device.switch.index')->with('success', 'Switch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdSwitch $switch): RedirectResponse
    {
        $switch->delete();

        return redirect()->route('active-device.switch.index')->with('success', 'Switch deleted successfully.');
    }
}
