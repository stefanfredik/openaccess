<?php

namespace Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\Site\Http\Requests\StoreSiteRequest;
use Modules\Site\Http\Requests\UpdateSiteRequest;
use Modules\Site\Models\Site;

class SiteController extends Controller
{
    use HasFlashMessages;

    public function index(\Illuminate\Http\Request $request): Response
    {
        $sites = Site::query()
            ->with(['area'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->input('area_id'), function ($query, $area_id) {
                if ($area_id !== 'all') {
                    $query->where('infrastructure_area_id', $area_id);
                }
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Site::Index', [
            'sites' => $sites,
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
            'filters' => $request->only(['search', 'area_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Site::Create', [
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
        ]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $site = Site::create($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('site-photos', 'public');
                $site->photos()->create([
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('site.index')->with('success', $this->flashCreated('site'));
    }

    public function show(Site $site): Response
    {
        return Inertia::render('Site::Show', [
            'site' => $site->load(['area', 'photos']),
        ]);
    }

    public function edit(Site $site): Response
    {
        return Inertia::render('Site::Edit', [
            'site' => $site->load(['photos']),
            'areas' => InfrastructureArea::select('id', 'name', 'code')->get(),
        ]);
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $site->update($request->validated());

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('site-photos', 'public');
                $site->photos()->create([
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('site.index')->with('success', $this->flashUpdated('site'));
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()->route('site.index')->with('success', $this->flashDeleted('site'));
    }
}
