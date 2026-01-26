<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\PassiveDevice\Models\Cable;
use Modules\PassiveDevice\Http\Requests\StoreCableRequest;
use Modules\PassiveDevice\Http\Requests\UpdateCableRequest;
use Modules\Area\Models\InfrastructureArea;

class CableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cables = Cable::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Cable/Index', [
            'cables' => $cables,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('PassiveDevice::Cable/Create', [
            'areas' => InfrastructureArea::all(),
            'types' => ['Single Mode', 'Multi Mode'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCableRequest $request)
    {
        Cable::create($request->validated());

        return redirect()->route('passive-device.cable.index')
            ->with('success', 'Cable created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $cable = Cable::with('area')->findOrFail($id);

        return Inertia::render('PassiveDevice::Cable/Show', [
            'cable' => $cable,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cable = Cable::findOrFail($id);

        return Inertia::render('PassiveDevice::Cable/Edit', [
            'cable' => $cable,
            'areas' => InfrastructureArea::all(),
            'types' => ['Single Mode', 'Multi Mode'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCableRequest $request, $id)
    {
        $cable = Cable::findOrFail($id);
        $cable->update($request->validated());

        return redirect()->route('passive-device.cable.index')
            ->with('success', 'Cable updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cable = Cable::findOrFail($id);
        $cable->delete();

        return redirect()->route('passive-device.cable.index')
            ->with('success', 'Cable deleted successfully.');
    }
}
