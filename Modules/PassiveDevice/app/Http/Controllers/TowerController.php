<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreTowerRequest;
use Modules\PassiveDevice\Http\Requests\UpdateTowerRequest;
use Modules\PassiveDevice\Models\Tower;

class TowerController extends Controller
{
    use HasFlashMessages;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $towers = Tower::query()
            ->with('area')
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->input('area_id'), function ($query, $area_id) {
                if ($area_id !== 'all') {
                    $query->where('infrastructure_area_id', $area_id);
                }
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('PassiveDevice::Tower/Index', [
            'towers' => $towers,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Tower/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
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
            return back()->with('success', $this->flashCreated('tower'));
        }

        return redirect()->route('passive-device.tower.index')
            ->with('success', $this->flashCreated('tower'));
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
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
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
            ->with('success', $this->flashUpdated('tower'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tower $tower): RedirectResponse
    {
        $tower->delete();

        return redirect()->route('passive-device.tower.index')
            ->with('success', $this->flashDeleted('tower'));
    }
}
