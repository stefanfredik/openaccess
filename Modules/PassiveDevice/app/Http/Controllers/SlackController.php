<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreSlackRequest;
use Modules\PassiveDevice\Http\Requests\UpdateSlackRequest;
use Modules\PassiveDevice\Models\Slack;

class SlackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $slacks = Slack::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Slack/Index', [
            'slacks' => $slacks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Slack/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSlackRequest $request): RedirectResponse
    {
        Slack::create($request->validated());

        return redirect()->route('passive-device.slack.index')
            ->with('success', 'Slack created successfully.');
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
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSlackRequest $request, Slack $slack): RedirectResponse
    {
        $slack->update($request->validated());

        return redirect()->route('passive-device.slack.index')
            ->with('success', 'Slack updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slack $slack): RedirectResponse
    {
        $slack->delete();

        return redirect()->route('passive-device.slack.index')
            ->with('success', 'Slack deleted successfully.');
    }
}
