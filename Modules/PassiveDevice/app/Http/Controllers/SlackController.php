<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\PassiveDevice\Models\Slack;
use Modules\PassiveDevice\Http\Requests\StoreSlackRequest;
use Modules\PassiveDevice\Http\Requests\UpdateSlackRequest;
use Modules\Area\Models\InfrastructureArea;

class SlackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $slacks = Slack::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Slack/Index', [
            'slacks' => $slacks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('PassiveDevice::Slack/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSlackRequest $request)
    {
        Slack::create($request->validated());

        return redirect()->route('passive-device.slack.index')
            ->with('success', 'Slack created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $slack = Slack::with('area')->findOrFail($id);

        return Inertia::render('PassiveDevice::Slack/Show', [
            'slack' => $slack,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $slack = Slack::findOrFail($id);

        return Inertia::render('PassiveDevice::Slack/Edit', [
            'slack' => $slack,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSlackRequest $request, $id)
    {
        $slack = Slack::findOrFail($id);
        $slack->update($request->validated());

        return redirect()->route('passive-device.slack.index')
            ->with('success', 'Slack updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $slack = Slack::findOrFail($id);
        $slack->delete();

        return redirect()->route('passive-device.slack.index')
            ->with('success', 'Slack deleted successfully.');
    }
}
