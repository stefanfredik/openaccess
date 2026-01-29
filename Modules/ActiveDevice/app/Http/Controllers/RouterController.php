<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreRouterRequest;
use Modules\ActiveDevice\Http\Requests\UpdateRouterRequest;
use Modules\ActiveDevice\Models\Router;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class RouterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $routers = Router::with(['area', 'pop'])->latest()->paginate(10);

        return Inertia::render('ActiveDevice::Router/Index', [
            'routers' => $routers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ActiveDevice::Router/Create', [
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRouterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;
        $router = Router::create($data);

        if ($request->has('service_ports')) {
            foreach ($request->service_ports as $portData) {
                $router->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'Router created successfully.');
        }

        return redirect()->route('active-device.router.index')->with('success', 'Router created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Router $router): Response
    {
        $allDevices = collect();
        $allDevices = $allDevices->merge(Router::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AdSwitch::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Olt::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Ont::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AccessPoint::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\Cpe\Models\Cpe::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));

        return Inertia::render('ActiveDevice::Router/Show', [
            'router' => $router->load(['area', 'pop', 'sourceConnections.destination', 'destinationConnections.source', 'servicePorts', 'interfaces']),
            'availableDevices' => $allDevices,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Router $router): Response
    {
        return Inertia::render('ActiveDevice::Router/Edit', [
            'router' => $router->load('servicePorts'),
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRouterRequest $request, Router $router): RedirectResponse
    {
        $router->update($request->validated());

        if ($request->has('service_ports')) {
            $router->servicePorts()->delete();
            foreach ($request->service_ports as $portData) {
                $router->servicePorts()->create(array_merge($portData, [
                    'company_id' => $request->user()->company_id,
                ]));
            }
        }

        return redirect()->route('active-device.router.index')->with('success', 'Router updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Router $router): RedirectResponse
    {
        $router->delete();

        return redirect()->route('active-device.router.index')->with('success', 'Router deleted successfully.');
    }
}
