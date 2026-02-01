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
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Cable/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $cable = Cable::create($data);

            // Generate Cores & Tubes Logic
            $coreCount = (int) $data['core_count'];
            $coresPerTube = 12; // Standard
            $tubeCount = ceil($coreCount / $coresPerTube);

            $tubeColors = ['Blue', 'Orange', 'Green', 'Brown', 'Grey', 'White', 'Red', 'Black', 'Yellow', 'Violet', 'Pink', 'Aqua'];
            $coreColors = ['Blue', 'Orange', 'Green', 'Brown', 'Grey', 'White', 'Red', 'Black', 'Yellow', 'Violet', 'Pink', 'Aqua'];

            $currentCore = 1;

            for ($t = 0; $t < $tubeCount; $t++) {
                $tube = \Modules\PassiveDevice\Models\CableTube::create([
                    'cable_id' => $cable->id,
                    'color' => $tubeColors[$t % 12],
                    'tube_number' => $t + 1,
                    'core_count' => ($t == $tubeCount - 1 && $coreCount % 12 != 0) ? ($coreCount % 12) : 12,
                ]);

                $coresInThisTube = $tube->core_count;

                for ($c = 0; $c < $coresInThisTube; $c++) {
                    \Modules\PassiveDevice\Models\CableCore::create([
                        'cable_id' => $cable->id,
                        'tube_id' => $tube->id,
                        'color' => $coreColors[$c % 12],
                        'core_number' => $currentCore++,
                        'status' => 'Available',
                    ]);
                }
            }
        });

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
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
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
