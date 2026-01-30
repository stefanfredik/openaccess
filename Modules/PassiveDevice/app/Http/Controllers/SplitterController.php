<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreSplitterRequest;
use Modules\PassiveDevice\Http\Requests\UpdateSplitterRequest;
use Modules\PassiveDevice\Models\Splitter;

class SplitterController extends Controller
{
    use HasFlashMessages;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $splitters = Splitter::query()
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

        return Inertia::render('PassiveDevice::Splitter/Index', [
            'splitters' => $splitters,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Splitter/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSplitterRequest $request): RedirectResponse
    {
        Splitter::create($request->validated());

        return redirect()->route('passive-device.splitter.index')
            ->with('success', $this->flashCreated('splitter'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Splitter $splitter): Response
    {
        return Inertia::render('PassiveDevice::Splitter/Show', [
            'splitter' => $splitter->load('area'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Splitter $splitter): Response
    {
        return Inertia::render('PassiveDevice::Splitter/Edit', [
            'splitter' => $splitter,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSplitterRequest $request, Splitter $splitter): RedirectResponse
    {
        $splitter->update($request->validated());

        return redirect()->route('passive-device.splitter.index')
            ->with('success', $this->flashUpdated('splitter'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Splitter $splitter): RedirectResponse
    {
        $splitter->delete();

        return redirect()->route('passive-device.splitter.index')
            ->with('success', $this->flashDeleted('splitter'));
    }
}
