<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StorePoleRequest;
use Modules\PassiveDevice\Http\Requests\UpdatePoleRequest;
use Modules\PassiveDevice\Models\Pole;

class PoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $poles = Pole::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Pole/Index', [
            'poles' => $poles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('PassiveDevice::Pole/Create', [
            'areas' => InfrastructureArea::all(),
            'materials' => ['Beton', 'Besi', 'Kayu'],
            'ownerships' => ['Sendiri', 'PLN', 'Sewa'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePoleRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        Pole::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'Pole created successfully.');
        }

        return redirect()->route('passive-device.pole.index')
            ->with('success', 'Pole created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $pole = Pole::with('area')->findOrFail($id);

        return Inertia::render('PassiveDevice::Pole/Show', [
            'pole' => $pole,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pole = Pole::findOrFail($id);

        return Inertia::render('PassiveDevice::Pole/Edit', [
            'pole' => $pole,
            'areas' => InfrastructureArea::all(),
            'materials' => ['Beton', 'Besi', 'Kayu'],
            'ownerships' => ['Sendiri', 'PLN', 'Sewa'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePoleRequest $request, $id)
    {
        $pole = Pole::findOrFail($id);
        $pole->update($request->validated());

        return redirect()->route('passive-device.pole.index')
            ->with('success', 'Pole updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pole = Pole::findOrFail($id);
        $pole->delete();

        return redirect()->route('passive-device.pole.index')
            ->with('success', 'Pole deleted successfully.');
    }
}
