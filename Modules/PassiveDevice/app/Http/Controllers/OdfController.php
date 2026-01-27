<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\PassiveDevice\Models\Odf;
use Modules\PassiveDevice\Http\Requests\StoreOdfRequest;
use Modules\PassiveDevice\Http\Requests\UpdateOdfRequest;
use Modules\Area\Models\InfrastructureArea;

class OdfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $odfs = Odf::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Odf/Index', [
            'odfs' => $odfs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('PassiveDevice::Odf/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOdfRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        Odf::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', 'ODF created successfully.');
        }

        return redirect()->route('passive-device.odf.index')
            ->with('success', 'ODF created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $odf = Odf::with('area')->findOrFail($id);

        return Inertia::render('PassiveDevice::Odf/Show', [
            'odf' => $odf,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $odf = Odf::findOrFail($id);

        return Inertia::render('PassiveDevice::Odf/Edit', [
            'odf' => $odf,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOdfRequest $request, $id)
    {
        $odf = Odf::findOrFail($id);
        $odf->update($request->validated());

        return redirect()->route('passive-device.odf.index')
            ->with('success', 'ODF updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $odf = Odf::findOrFail($id);
        $odf->delete();

        return redirect()->route('passive-device.odf.index')
            ->with('success', 'ODF deleted successfully.');
    }
}
