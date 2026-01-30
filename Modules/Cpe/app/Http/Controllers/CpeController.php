<?php

namespace Modules\Cpe\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Models\Ont;
use Modules\ActiveDevice\Models\Router;
use Modules\ActiveDevice\Services\DeviceService;
use Modules\Area\Models\InfrastructureArea;
use Modules\Cpe\Http\Requests\StoreCpeRequest;
use Modules\Cpe\Http\Requests\UpdateCpeRequest;
use Modules\Cpe\Models\Cpe;

class CpeController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private readonly DeviceService $deviceService
    ) {}

    public function index(Request $request): Response
    {
        $cpes = Cpe::query()
            ->with([
                'area',
                'sourceConnections.destination',
                'sourceConnections.destinationInterface',
                'sourceConnections.sourceInterface',
                'destinationConnections.source',
                'destinationConnections.sourceInterface',
                'destinationConnections.destinationInterface',
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->area_id && $request->area_id !== 'all', function ($query, $areaId) {
                $query->where('infrastructure_area_id', $areaId);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Cpe::Index', [
            'cpes' => $cpes,
            'areas' => InfrastructureArea::all(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Cpe::Create', [
            'areas' => InfrastructureArea::all(),
            'onts' => Ont::all(), // For uplink selection
            'routers' => Router::all(),
        ]);
    }

    public function store(StoreCpeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        Cpe::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', $this->flashCreated('CPE'));
        }

        return redirect()->route('cpe.index')->with('success', $this->flashCreated('CPE'));
    }

    public function show(Cpe $cpe): Response
    {
        return Inertia::render('Cpe::Show', [
            'cpe' => $cpe->load(['area', 'activeDevice', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface']),
            'availableDevices' => $this->deviceService->getAllDevices(),
        ]);
    }

    public function edit(Cpe $cpe): Response
    {
        return Inertia::render('Cpe::Edit', [
            'cpe' => $cpe,
            'areas' => InfrastructureArea::all(),
            'onts' => Ont::all(),
            'routers' => Router::all(),
        ]);
    }

    public function update(UpdateCpeRequest $request, Cpe $cpe): RedirectResponse
    {
        $cpe->update($request->validated());

        return redirect()->route('cpe.index')->with('success', $this->flashUpdated('CPE'));
    }

    public function destroy(Cpe $cpe): RedirectResponse
    {
        $cpe->delete();

        return redirect()->route('cpe.index')->with('success', $this->flashDeleted('CPE'));
    }
}
