<?php

namespace Modules\Area\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Area\Models\InfrastructureArea;
use Modules\Area\Http\Requests\StoreAreaRequest;
use Modules\Area\Http\Requests\UpdateAreaRequest;

class AreaController extends Controller
{
    public function index()
    {
        $areas = InfrastructureArea::query()
            ->latest()
            ->get();

        return Inertia::render('Area::Index', [
            'areas' => $areas,
        ]);
    }

    public function create()
    {
        return Inertia::render('Area::Create');
    }

    public function store(StoreAreaRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        InfrastructureArea::create($data);

        return redirect()->route('area.index')->with('success', 'Area created successfully.');
    }

    public function show($id)
    {
        $area = InfrastructureArea::findOrFail($id);

        return Inertia::render('Area::Show', [
            'area' => $area,
        ]);
    }

    public function edit($id)
    {
        $area = InfrastructureArea::findOrFail($id);

        return Inertia::render('Area::Edit', [
            'area' => $area,
        ]);
    }

    public function update(UpdateAreaRequest $request, $id)
    {
        $area = InfrastructureArea::findOrFail($id);
        $area->update($request->validated());

        return redirect()->route('area.index')->with('success', 'Area updated successfully.');
    }

    public function destroy($id)
    {
        $area = InfrastructureArea::findOrFail($id);
        $area->delete();

        return redirect()->route('area.index')->with('success', 'Area deleted successfully.');
    }
}
