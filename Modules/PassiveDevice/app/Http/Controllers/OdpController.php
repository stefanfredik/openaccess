<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\PassiveDevice\Models\Odp;
use Modules\PassiveDevice\Http\Requests\StoreOdpRequest;
use Modules\PassiveDevice\Http\Requests\UpdateOdpRequest;
use Modules\Area\Models\InfrastructureArea;

class OdpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $odps = Odp::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Odp/Index', [
            'odps' => $odps,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('PassiveDevice::Odp/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOdpRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        Odp::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'ODP created successfully.');
        }

        return redirect()->route('passive-device.odps.index')
            ->with('success', 'ODP created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $odp = Odp::with('area')->findOrFail($id);

        return Inertia::render('PassiveDevice::Odp/Show', [
            'odp' => $odp,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $odp = Odp::findOrFail($id);

        return Inertia::render('PassiveDevice::Odp/Edit', [
            'odp' => $odp,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOdpRequest $request, $id)
    {
        $odp = Odp::findOrFail($id);
        $odp->update($request->validated());

        return redirect()->route('passive-device.odps.index')
            ->with('success', 'ODP updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $odp = Odp::findOrFail($id);
        $odp->delete();

        return redirect()->route('passive-device.odps.index')
            ->with('success', 'ODP deleted successfully.');
    }
}
