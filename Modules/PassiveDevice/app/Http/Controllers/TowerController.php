<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreTowerRequest;
use Modules\PassiveDevice\Http\Requests\UpdateTowerRequest;
use Modules\PassiveDevice\Models\Tower;

class TowerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $towers = Tower::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Tower/Index', [
            'towers' => $towers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Tower/Create', [
            'areas' => InfrastructureArea::all(),
            'types' => ['SST', 'Monopole', 'Guyed'],
            'ownerships' => ['Sendiri', 'Sewa'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTowerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        Tower::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'Tower created successfully.');
        }

        return redirect()->route('passive-device.tower.index')
            ->with('success', 'Tower created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Tower $tower): Response
    {
        return Inertia::render('PassiveDevice::Tower/Show', [
            'tower' => $tower->load('area'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tower $tower): Response
    {
        return Inertia::render('PassiveDevice::Tower/Edit', [
            'tower' => $tower,
            'areas' => InfrastructureArea::all(),
            'types' => ['SST', 'Monopole', 'Guyed'],
            'ownerships' => ['Sendiri', 'Sewa'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTowerRequest $request, Tower $tower): RedirectResponse
    {
        $tower->update($request->validated());

        return redirect()->route('passive-device.tower.index')
            ->with('success', 'Tower updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tower $tower): RedirectResponse
    {
        $tower->delete();

        return redirect()->route('passive-device.tower.index')
            ->with('success', 'Tower deleted successfully.');
    }
}
