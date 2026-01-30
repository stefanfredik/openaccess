<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreJointBoxRequest;
use Modules\PassiveDevice\Http\Requests\UpdateJointBoxRequest;
use Modules\PassiveDevice\Models\JointBox;

class JointBoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $jointBoxes = JointBox::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::JointBox/Index', [
            'jointBoxes' => $jointBoxes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::JointBox/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJointBoxRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        JointBox::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'Joint Box created successfully.');
        }

        return redirect()->route('passive-device.joint-box.index')
            ->with('success', 'Joint Box created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show(JointBox $jointBox): Response
    {
        return Inertia::render('PassiveDevice::JointBox/Show', [
            'jointBox' => $jointBox->load('area'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JointBox $jointBox): Response
    {
        return Inertia::render('PassiveDevice::JointBox/Edit', [
            'jointBox' => $jointBox,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJointBoxRequest $request, JointBox $jointBox): RedirectResponse
    {
        $jointBox->update($request->validated());

        return redirect()->route('passive-device.joint-box.index')
            ->with('success', 'Joint Box updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JointBox $jointBox): RedirectResponse
    {
        $jointBox->delete();

        return redirect()->route('passive-device.joint-box.index')
            ->with('success', 'Joint Box deleted successfully.');
    }
}
