<?php

namespace Modules\Pop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\Pop\Models\Pop;
use Modules\Pop\Models\PopPhoto;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Http\Requests\StorePopRequest;
use Modules\Pop\Http\Requests\UpdatePopRequest;

class PopController extends Controller
{
    public function index()
    {
        $pops = Pop::query()
            ->with(['area'])
            ->latest()
            ->get();

        return Inertia::render('Pop::Index', [
            'pops' => $pops,
        ]);
    }

    public function create()
    {
        return Inertia::render('Pop::Create', [
            'areas' => InfrastructureArea::all(),
        ]);
    }

    public function store(StorePopRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $pop = Pop::create($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('pop-photos', 'public');
                $pop->photos()->create([
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('pop.index')->with('success', 'POP created successfully.');
    }

    public function show($id)
    {
        $pop = Pop::with(['area', 'photos'])->findOrFail($id);

        return Inertia::render('Pop::Show', [
            'pop' => $pop,
        ]);
    }

    public function edit($id)
    {
        $pop = Pop::with(['photos'])->findOrFail($id);

        return Inertia::render('Pop::Edit', [
            'pop' => $pop,
            'areas' => InfrastructureArea::all(),
        ]);
    }

    public function update(UpdatePopRequest $request, $id)
    {
        $pop = Pop::findOrFail($id);
        $pop->update($request->validated());

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('pop-photos', 'public');
                $pop->photos()->create([
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('pop.index')->with('success', 'POP updated successfully.');
    }

    public function destroy($id)
    {
        $pop = Pop::findOrFail($id);
        $pop->delete();

        return redirect()->route('pop.index')->with('success', 'POP deleted successfully.');
    }
}
