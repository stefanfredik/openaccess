<?php

namespace Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\Site\Http\Requests\StoreSiteRequest;
use Modules\Site\Http\Requests\UpdateSiteRequest;
use Modules\Site\Models\Site;

class SiteController extends Controller
{
    public function index(): Response
    {
        $sites = Site::query()
            ->with(['area'])
            ->latest()
            ->get();

        return Inertia::render('Site::Index', [
            'sites' => $sites,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Site::Create', [
            'areas' => InfrastructureArea::all(),
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

        return redirect()->route('site.index')->with('success', 'Site created successfully.');
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
            'areas' => InfrastructureArea::all(),
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

        return redirect()->route('site.index')->with('success', 'Site updated successfully.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()->route('site.index')->with('success', 'Site deleted successfully.');
    }
}
