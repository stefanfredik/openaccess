<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StorePoleRequest;
use Modules\PassiveDevice\Http\Requests\UpdatePoleRequest;
use Modules\PassiveDevice\Models\Pole;

class PoleController extends Controller
{
    use HasFlashMessages;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $poles = Pole::query()
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

        return Inertia::render('PassiveDevice::Pole/Index', [
            'poles' => $poles,
            'areas' => InfrastructureArea::all(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
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
    public function store(StorePoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        Pole::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', $this->flashCreated('pole'));
        }

        return redirect()->route('passive-device.pole.index')
            ->with('success', $this->flashCreated('pole'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Pole $pole): Response
    {
        return Inertia::render('PassiveDevice::Pole/Show', [
            'pole' => $pole->load('area'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pole $pole): Response
    {
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
    public function update(UpdatePoleRequest $request, Pole $pole): RedirectResponse
    {
        $pole->update($request->validated());

        return redirect()->route('passive-device.pole.index')
            ->with('success', $this->flashUpdated('pole'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pole $pole): RedirectResponse
    {
        $pole->delete();

        return redirect()->route('passive-device.pole.index')
            ->with('success', $this->flashDeleted('pole'));
    }
}
