<?php

namespace Modules\Area\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Models\Olt;
use Modules\Area\Http\Requests\StoreAreaRequest;
use Modules\Area\Http\Requests\UpdateAreaRequest;
use Modules\Area\Models\InfrastructureArea;
use Modules\Cpe\Models\Cpe;
use Modules\PassiveDevice\Models\Odp;

class AreaController extends Controller
{
    use HasFlashMessages;

    public function index(Request $request): Response
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
            ->paginate(10);

        return Inertia::render('Area::Index', [
            'areas' => $areas,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Area::Create');
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! $request->user()->company_id) {
            return back()->with('error', 'User yang sedang aktif belum terhubung ke Company.');
        }

        $data['company_id'] = $request->user()->company_id;

        InfrastructureArea::create($data);

        return redirect()->route('area.index')->with('success', $this->flashCreated('area'));
    }

    public function show(InfrastructureArea $area): Response
    {
        $stats = [
            'pops_count' => $area->pops()->count(),
            'servers_count' => $area->servers()->count(),
            'olts_count' => Olt::where('infrastructure_area_id', $area->id)->count(),
            'odps_count' => Odp::where('infrastructure_area_id', $area->id)->count(),
            'cpes_count' => Cpe::where('infrastructure_area_id', $area->id)->count(),
        ];

        return Inertia::render('Area::Show', [
            'area' => $area,
            'stats' => $stats,
        ]);
    }

    public function edit(InfrastructureArea $area): Response
    {
        return Inertia::render('Area::Edit', [
            'area' => $area,
        ]);
    }

    public function update(UpdateAreaRequest $request, InfrastructureArea $area): RedirectResponse
    {
        $area->update($request->validated());

        return redirect()->route('area.index')->with('success', $this->flashUpdated('area'));
    }

    public function destroy(InfrastructureArea $area): RedirectResponse
    {
        if ($area->pops()->exists() || $area->servers()->exists()) {
            return back()->with('error', $this->flashDeleteHasRelations('area'));
        }

        $area->delete();

        return redirect()->route('area.index')->with('success', $this->flashDeleted('area'));
    }
}
