<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\PassiveDevice\Models\SplicingPoint;
use Modules\PassiveDevice\Models\JointBox;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreSplicingPointRequest;
use Modules\PassiveDevice\Http\Requests\UpdateSplicingPointRequest;

class SplicingPointController extends Controller
{
    public function index(): Response
    {
        $splicingPoints = SplicingPoint::query()
            ->with(['area', 'jointBox'])
            ->latest()
            ->paginate(10);

        return Inertia::render('PassiveDevice::SplicingPoint/Index', [
            'splicingPoints' => $splicingPoints,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PassiveDevice::SplicingPoint/Create', [
            'areas' => InfrastructureArea::all(),
            'jointBoxes' => JointBox::all(),
        ]);
    }

    public function store(StoreSplicingPointRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        SplicingPoint::create($data);

        return redirect()->route('passive-device.splicing-point.index')->with('success', 'Splicing Point created successfully.');
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
            'areas' => InfrastructureArea::all(),
            'jointBoxes' => JointBox::all(),
        ]);
    }

    public function update(UpdateSplicingPointRequest $request, SplicingPoint $splicingPoint): RedirectResponse
    {
        $splicingPoint->update($request->validated());

        return redirect()->route('passive-device.splicing-point.index')->with('success', 'Splicing Point updated successfully.');
    }

    public function destroy(SplicingPoint $splicingPoint): RedirectResponse
    {
        $splicingPoint->delete();

        return redirect()->route('passive-device.splicing-point.index')->with('success', 'Splicing Point deleted successfully.');
    }
}
