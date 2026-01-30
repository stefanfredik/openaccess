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
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type']);
        
        $areas = InfrastructureArea::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, function ($query, $type) {
                $query->where('type', $type);
            })
            ->latest()
            ->get();

        return Inertia::render('Area::Index', [
            'areas' => $areas,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return Inertia::render('Area::Create');
    }

    public function store(StoreAreaRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->company_id) {
            return back()->with('error', 'User yang sedang aktif belum terhubung ke Company.');
        }

        $data['company_id'] = $request->user()->company_id;

        InfrastructureArea::create($data);

        return redirect()->route('area.index')->with('success', 'Area created successfully.');
    }

    public function show($id)
    {
        $area = InfrastructureArea::findOrFail($id);

        $stats = [
            'pops_count' => $area->pops()->count(),
            'servers_count' => $area->servers()->count(),
            'olts_count' => \Modules\ActiveDevice\Models\Olt::where('infrastructure_area_id', $area->id)->count(),
            'odps_count' => \Modules\PassiveDevice\Models\Odp::where('infrastructure_area_id', $area->id)->count(),
            'cpes_count' => \Modules\Cpe\Models\Cpe::where('infrastructure_area_id', $area->id)->count(),
        ];

        return Inertia::render('Area::Show', [
            'area' => $area,
            'stats' => $stats,
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

        if ($area->pops()->exists() || $area->servers()->exists()) {
            return back()->with('error', 'Wilayah tidak dapat dihapus karena masih memiliki data terkait (POP/Server).');
        }

        $area->delete();

        return redirect()->route('area.index')->with('success', 'Area deleted successfully.');
    }
}
