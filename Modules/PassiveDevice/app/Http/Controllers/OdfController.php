<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreOdfRequest;
use Modules\PassiveDevice\Http\Requests\UpdateOdfRequest;
use Modules\PassiveDevice\Models\Odf;

class OdfController extends Controller
{
    use HasFlashMessages;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $odfs = Odf::query()
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

        return Inertia::render('PassiveDevice::Odf/Index', [
            'odfs' => $odfs,
            'areas' => InfrastructureArea::all(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Odf/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOdfRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        Odf::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', $this->flashCreated('odf'));
        }

        return redirect()->route('passive-device.odf.index')
            ->with('success', $this->flashCreated('odf'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Odf $odf): Response
    {
        return Inertia::render('PassiveDevice::Odf/Show', [
            'odf' => $odf->load('area'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Odf $odf): Response
    {
        return Inertia::render('PassiveDevice::Odf/Edit', [
            'odf' => $odf,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOdfRequest $request, Odf $odf): RedirectResponse
    {
        $odf->update($request->validated());

        return redirect()->route('passive-device.odf.index')
            ->with('success', $this->flashUpdated('odf'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Odf $odf): RedirectResponse
    {
        $odf->delete();

        return redirect()->route('passive-device.odf.index')
            ->with('success', $this->flashDeleted('odf'));
    }
}
