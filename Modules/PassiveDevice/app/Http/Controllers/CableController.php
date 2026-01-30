<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreCableRequest;
use Modules\PassiveDevice\Http\Requests\UpdateCableRequest;
use Modules\PassiveDevice\Models\Cable;

class CableController extends Controller
{
    use HasFlashMessages;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $cables = Cable::query()
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

        return Inertia::render('PassiveDevice::Cable/Index', [
            'cables' => $cables,
            'areas' => InfrastructureArea::all(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Cable/Create', [
            'areas' => InfrastructureArea::all(),
            'types' => ['Single Mode', 'Multi Mode'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCableRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        Cable::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', $this->flashCreated('cable'));
        }

        return redirect()->route('passive-device.cable.index')
            ->with('success', $this->flashCreated('cable'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Cable $cable): Response
    {
        return Inertia::render('PassiveDevice::Cable/Show', [
            'cable' => $cable->load('area'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cable $cable): Response
    {
        return Inertia::render('PassiveDevice::Cable/Edit', [
            'cable' => $cable,
            'areas' => InfrastructureArea::all(),
            'types' => ['Single Mode', 'Multi Mode'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCableRequest $request, Cable $cable): RedirectResponse
    {
        $cable->update($request->validated());

        return redirect()->route('passive-device.cable.index')
            ->with('success', $this->flashUpdated('cable'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cable $cable): RedirectResponse
    {
        $cable->delete();

        return redirect()->route('passive-device.cable.index')
            ->with('success', $this->flashDeleted('cable'));
    }
}
