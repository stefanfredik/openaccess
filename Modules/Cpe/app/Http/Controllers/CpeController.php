<?php

namespace Modules\Cpe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Cpe\Models\Cpe;
use Modules\Area\Models\InfrastructureArea;
use Modules\ActiveDevice\Models\Ont;
use Modules\ActiveDevice\Models\Router;
use Modules\Cpe\Http\Requests\StoreCpeRequest;
use Modules\Cpe\Http\Requests\UpdateCpeRequest;

class CpeController extends Controller
{
    public function index(): Response
    {
        $cpes = Cpe::query()
            ->with(['area'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Cpe::Index', [
            'cpes' => $cpes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Cpe::Create', [
            'areas' => InfrastructureArea::all(),
            'onts' => Ont::all(), // For uplink selection
            'routers' => Router::all(),
        ]);
    }

    public function store(StoreCpeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        Cpe::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'CPE created successfully.');
        }

        return redirect()->route('cpe.index')->with('success', 'CPE created successfully.');
    }

    public function show(Cpe $cpe): Response
    {
        $allDevices = collect();
        $allDevices = $allDevices->merge(Router::all()->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AdSwitch::all()->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Olt::all()->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\Ont::all()->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(\Modules\ActiveDevice\Models\AccessPoint::all()->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));
        $allDevices = $allDevices->merge(Cpe::all()->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'type' => get_class($d)]));

        return Inertia::render('Cpe::Show', [
            'cpe' => $cpe->load(['area', 'activeDevice', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface']),
            'availableDevices' => $allDevices,
        ]);
    }

    public function edit(Cpe $cpe): Response
    {
        return Inertia::render('Cpe::Edit', [
            'cpe' => $cpe,
            'areas' => InfrastructureArea::all(),
            'onts' => Ont::all(),
            'routers' => Router::all(),
        ]);
    }

    public function update(UpdateCpeRequest $request, Cpe $cpe): RedirectResponse
    {
        $cpe->update($request->validated());

        return redirect()->route('cpe.index')->with('success', 'CPE updated successfully.');
    }

    public function destroy(Cpe $cpe): RedirectResponse
    {
        $cpe->delete();

        return redirect()->route('cpe.index')->with('success', 'CPE deleted successfully.');
    }
}
