<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreOntRequest;
use Modules\ActiveDevice\Http\Requests\UpdateOntRequest;
use Modules\ActiveDevice\Models\Ont;
use Modules\ActiveDevice\Models\Router;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class OntController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $onts = Ont::with([
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
        ])->latest()->paginate(10);

        return Inertia::render('ActiveDevice::Ont/Index', [
            'onts' => $onts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ActiveDevice::Ont/Create', [
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOntRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;
        $ont = Ont::create($data);

        if ($request->has('service_ports')) {
            foreach ($request->service_ports as $portData) {
                $ont->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'ONT created successfully.');
        }

        return redirect()->route('active-device.ont.index')->with('success', 'ONT created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Ont $ont): Response
    {
        $allDevices = collect();
        $allDevices = $allDevices->merge(Router::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AdSwitch::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Olt::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(Ont::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AccessPoint::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\Cpe\Models\Cpe::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));

        return Inertia::render('ActiveDevice::Ont/Show', [
            'ont' => $ont->load(['area', 'pop', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'servicePorts', 'interfaces']),
            'availableDevices' => $allDevices,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ont $ont): Response
    {
        return Inertia::render('ActiveDevice::Ont/Edit', [
            'ont' => $ont->load('servicePorts'),
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOntRequest $request, Ont $ont): RedirectResponse
    {
        $ont->update($request->validated());

        if ($request->has('service_ports')) {
            $ont->servicePorts()->delete();
            foreach ($request->service_ports as $portData) {
                $ont->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

        return redirect()->route('active-device.ont.index')->with('success', 'ONT updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ont $ont): RedirectResponse
    {
        $ont->delete();

        return redirect()->route('active-device.ont.index')->with('success', 'ONT deleted successfully.');
    }
}
