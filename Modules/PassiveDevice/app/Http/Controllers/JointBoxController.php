<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreJointBoxRequest;
use Modules\PassiveDevice\Http\Requests\UpdateJointBoxRequest;
use Modules\PassiveDevice\Models\JointBox;

class JointBoxController extends Controller
{
    use HasFlashMessages;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $jointBoxes = JointBox::query()
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

        return Inertia::render('PassiveDevice::JointBox/Index', [
            'jointBoxes' => $jointBoxes,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::JointBox/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
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
            return back()->with('success', $this->flashCreated('joint_box'));
        }

        return redirect()->route('passive-device.joint-box.index')
            ->with('success', $this->flashCreated('joint_box'));
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
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJointBoxRequest $request, JointBox $jointBox): RedirectResponse
    {
        $jointBox->update($request->validated());

        return redirect()->route('passive-device.joint-box.index')
            ->with('success', $this->flashUpdated('joint_box'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JointBox $jointBox): RedirectResponse
    {
        $jointBox->delete();

        return redirect()->route('passive-device.joint-box.index')
            ->with('success', $this->flashDeleted('joint_box'));
    }
}
