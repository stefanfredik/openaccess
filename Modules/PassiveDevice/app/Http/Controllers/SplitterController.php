<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreSplitterRequest;
use Modules\PassiveDevice\Http\Requests\UpdateSplitterRequest;
use Modules\PassiveDevice\Models\Splitter;

class SplitterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $splitters = Splitter::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Splitter/Index', [
            'splitters' => $splitters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('PassiveDevice::Splitter/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSplitterRequest $request)
    {
        Splitter::create($request->validated());

        return redirect()->route('passive-device.splitter.index')
            ->with('success', 'Splitter created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $splitter = Splitter::with('area')->findOrFail($id);

        return Inertia::render('PassiveDevice::Splitter/Show', [
            'splitter' => $splitter,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $splitter = Splitter::findOrFail($id);

        return Inertia::render('PassiveDevice::Splitter/Edit', [
            'splitter' => $splitter,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSplitterRequest $request, $id)
    {
        $splitter = Splitter::findOrFail($id);
        $splitter->update($request->validated());

        return redirect()->route('passive-device.splitter.index')
            ->with('success', 'Splitter updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $splitter = Splitter::findOrFail($id);
        $splitter->delete();

        return redirect()->route('passive-device.splitter.index')
            ->with('success', 'Splitter deleted successfully.');
    }
}
