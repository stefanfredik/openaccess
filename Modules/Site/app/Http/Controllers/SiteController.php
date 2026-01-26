<?php

namespace Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\Site\Models\Site;
use Modules\Site\Http\Requests\StoreSiteRequest;
use Modules\Site\Http\Requests\UpdateSiteRequest;
use Modules\Area\Models\InfrastructureArea;

use Modules\Site\Models\SitePhoto;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::query()
            ->with(['area'])
            ->latest()
            ->get();

        return Inertia::render('Site::Index', [
            'sites' => $sites,
        ]);
    }

    public function create()
    {
        return Inertia::render('Site::Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    public function store(StoreSiteRequest $request)
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

    public function show($id)
    {
        $site = Site::with(['area', 'photos'])->findOrFail($id);

        return Inertia::render('Site::Show', [
            'site' => $site,
        ]);
    }

    public function edit($id)
    {
        $site = Site::with(['photos'])->findOrFail($id);

        return Inertia::render('Site::Edit', [
            'site' => $site,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    public function update(UpdateSiteRequest $request, $id)
    {
        $site = Site::findOrFail($id);
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

    public function destroy($id)
    {
        $site = Site::findOrFail($id);
        $site->delete();

        return redirect()->route('site.index')->with('success', 'Site deleted successfully.');
    }
}
