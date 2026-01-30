<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ActiveDevice\Http\Requests\StoreOntRequest;
use Modules\ActiveDevice\Http\Requests\UpdateOntRequest;
use Modules\ActiveDevice\Models\Ont;
use Modules\ActiveDevice\Services\DeviceService;
use Modules\ActiveDevice\Services\OntService;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;

class OntController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private readonly DeviceService $deviceService,
        private readonly OntService $ontService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): Response
    {
        $onts = Ont::query()
            ->with([
                'area',
                'pop',
                'servicePorts',
                'interfaces',
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

        return Inertia::render('ActiveDevice::Ont/Index', [
            'onts' => $onts,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ActiveDevice::Ont/Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'pops' => Pop::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOntRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        $this->ontService->create(
            $data,
            null,
            $request->service_ports
        );

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', $this->flashCreated('ont'));
        }

        return redirect()->route('active-device.ont.index')->with('success', $this->flashCreated('ont'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Ont $ont): Response
    {
        return Inertia::render('ActiveDevice::Ont/Show', [
            'ont' => $ont->load(['area', 'pop', 'sourceConnections.destination', 'sourceConnections.destinationInterface', 'destinationConnections.source', 'destinationConnections.sourceInterface', 'servicePorts', 'interfaces']),
            'availableDevices' => $this->deviceService->getAllDevices(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ont $ont): Response
    {
        return Inertia::render('ActiveDevice::Ont/Edit', [
            'ont' => $ont->load('servicePorts'),
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'pops' => Pop::select('id', 'name', 'code')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOntRequest $request, Ont $ont): RedirectResponse
    {
        $this->ontService->update(
            $ont,
            $request->validated(),
            null,
            $request->has('service_ports') ? $request->service_ports : null,
            $request->user()->company_id
        );

        return redirect()->route('active-device.ont.index')->with('success', $this->flashUpdated('ont'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ont $ont): RedirectResponse
    {
        $this->ontService->delete($ont);

        return redirect()->route('active-device.ont.index')->with('success', $this->flashDeleted('ont'));
    }
}
