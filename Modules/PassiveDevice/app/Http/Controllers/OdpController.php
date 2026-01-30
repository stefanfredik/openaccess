<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\PassiveDevice\Http\Requests\StoreOdpRequest;
use Modules\PassiveDevice\Http\Requests\UpdateOdpRequest;
use Modules\PassiveDevice\Models\Odp;

class OdpController extends Controller
{
    use HasFlashMessages;

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $odps = Odp::with('area')->latest()->paginate(10);

        return Inertia::render('PassiveDevice::Odp/Index', [
            'odps' => $odps,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PassiveDevice::Odp/Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOdpRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        Odp::create($data);

        if ($request->header('referer') && str_contains($request->header('referer'), route('map.index'))) {
            return back()->with('success', $this->flashCreated('odp'));
        }

        return redirect()->route('passive-device.odps.index')
            ->with('success', $this->flashCreated('odp'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Odp $odp): Response
    {
        return Inertia::render('PassiveDevice::Odp/Show', [
            'odp' => $odp->load('area'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Odp $odp): Response
    {
        return Inertia::render('PassiveDevice::Odp/Edit', [
            'odp' => $odp,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOdpRequest $request, Odp $odp): RedirectResponse
    {
        $odp->update($request->validated());

        return redirect()->route('passive-device.odps.index')
            ->with('success', $this->flashUpdated('odp'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Odp $odp): RedirectResponse
    {
        $odp->delete();

        return redirect()->route('passive-device.odps.index')
            ->with('success', $this->flashDeleted('odp'));
    }
}
