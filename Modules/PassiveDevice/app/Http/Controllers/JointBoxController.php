<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\PassiveDevice\Models\JointBox;
use Modules\PassiveDevice\Http\Requests\StoreJointBoxRequest;
use Modules\PassiveDevice\Http\Requests\UpdateJointBoxRequest;
use Modules\Area\Models\InfrastructureArea;

class JointBoxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jointBoxes = JointBox::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::JointBox/Index', [
            'jointBoxes' => $jointBoxes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('PassiveDevice::JointBox/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJointBoxRequest $request)
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
    public function show($id)
    {
        $jointBox = JointBox::with('area')->findOrFail($id);

        return Inertia::render('PassiveDevice::JointBox/Show', [
            'jointBox' => $jointBox,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $jointBox = JointBox::findOrFail($id);

        return Inertia::render('PassiveDevice::JointBox/Edit', [
            'jointBox' => $jointBox,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJointBoxRequest $request, $id)
    {
        $jointBox = JointBox::findOrFail($id);
        $jointBox->update($request->validated());

        return redirect()->route('passive-device.joint-box.index')
            ->with('success', 'Joint Box updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jointBox = JointBox::findOrFail($id);
        $jointBox->delete();

        return redirect()->route('passive-device.joint-box.index')
            ->with('success', 'Joint Box deleted successfully.');
    }
}
