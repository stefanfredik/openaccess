<?php

namespace Modules\Pop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Http\Requests\StorePopRequest;
use Modules\Pop\Http\Requests\UpdatePopRequest;
use Modules\Pop\Models\Pop;
use Modules\Pop\Services\PopService;

class PopController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private readonly PopService $popService
    ) {}

    public function index(Request $request): Response
    {
        $pops = Pop::query()
            ->with(['area'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->input('type'), function ($query, $type) {
                if ($type !== 'all') {
                    $query->where('status', $type);
                }
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Pop::Index', [
            'pops' => $pops,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pop::Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    public function store(StorePopRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        $this->popService->create(
            $data,
            $request->file('photos')
        );

        return redirect()->route('pop.index')->with('success', $this->flashCreated('pop'));
    }

    public function show(Pop $pop): Response
    {
        return Inertia::render('Pop::Show', [
            'pop' => $pop->load(['area', 'photos']),
        ]);
    }

    public function edit(Pop $pop): Response
    {
        return Inertia::render('Pop::Edit', [
            'pop' => $pop->load(['photos']),
            'areas' => InfrastructureArea::all(),
        ]);
    }

    public function update(UpdatePopRequest $request, Pop $pop): RedirectResponse
    {
        $this->popService->update(
            $pop,
            $request->validated(),
            $request->file('photos')
        );

        return redirect()->route('pop.index')->with('success', $this->flashUpdated('pop'));
    }

    public function destroy(Pop $pop): RedirectResponse
    {
        $this->popService->delete($pop);

        return redirect()->route('pop.index')->with('success', $this->flashDeleted('pop'));
    }
}
