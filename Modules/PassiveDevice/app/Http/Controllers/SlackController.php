<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreSlackRequest;
use Modules\PassiveDevice\Http\Requests\UpdateSlackRequest;
use Modules\PassiveDevice\Models\Slack;

class SlackController extends Controller
{
    use HasFlashMessages;

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $slacks = Slack::query()
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

        return Inertia::render('PassiveDevice::Slack/Index', [
            'slacks' => $slacks,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Slack/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSlackRequest $request): RedirectResponse
    {
        Slack::create($request->validated());

        return redirect()->route('passive-device.slack.index')
            ->with('success', $this->flashCreated('slack'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Slack $slack): Response
    {
        return Inertia::render('PassiveDevice::Slack/Show', [
            'slack' => $slack->load('area'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slack $slack): Response
    {
        return Inertia::render('PassiveDevice::Slack/Edit', [
            'slack' => $slack,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSlackRequest $request, Slack $slack): RedirectResponse
    {
        $slack->update($request->validated());

        return redirect()->route('passive-device.slack.index')
            ->with('success', $this->flashUpdated('slack'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slack $slack): RedirectResponse
    {
        $slack->delete();

        return redirect()->route('passive-device.slack.index')
            ->with('success', $this->flashDeleted('slack'));
    }
}
