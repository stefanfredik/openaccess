<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreSplicingPointRequest;
use Modules\PassiveDevice\Http\Requests\UpdateSplicingPointRequest;
use Modules\PassiveDevice\Models\JointBox;
use Modules\PassiveDevice\Models\SplicingPoint;

class SplicingPointController extends Controller
{
    use HasFlashMessages;

    public function index(\Illuminate\Http\Request $request): Response
    {
        $splicingPoints = SplicingPoint::query()
            ->with(['area', 'jointBox'])
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

        return Inertia::render('PassiveDevice::SplicingPoint/Index', [
            'splicingPoints' => $splicingPoints,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PassiveDevice::SplicingPoint/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'jointBoxes' => JointBox::select('id', 'name', 'code')->get(),
        ]);
    }

    public function store(StoreSplicingPointRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        SplicingPoint::create($data);

        return redirect()->route('passive-device.splicing-point.index')->with('success', $this->flashCreated('splicing_point'));
    }

    public function show(SplicingPoint $splicingPoint): Response
    {
        return Inertia::render('PassiveDevice::SplicingPoint/Show', [
            'splicingPoint' => $splicingPoint->load(['area', 'jointBox']),
        ]);
    }

    public function edit(SplicingPoint $splicingPoint): Response
    {
        return Inertia::render('PassiveDevice::SplicingPoint/Edit', [
            'splicingPoint' => $splicingPoint,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'jointBoxes' => JointBox::select('id', 'name', 'code')->get(),
        ]);
    }

    public function update(UpdateSplicingPointRequest $request, SplicingPoint $splicingPoint): RedirectResponse
    {
        $splicingPoint->update($request->validated());

        return redirect()->route('passive-device.splicing-point.index')->with('success', $this->flashUpdated('splicing_point'));
    }

    public function destroy(SplicingPoint $splicingPoint): RedirectResponse
    {
        $splicingPoint->delete();

        return redirect()->route('passive-device.splicing-point.index')->with('success', $this->flashDeleted('splicing_point'));
    }
}
