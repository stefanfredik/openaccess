<?php

namespace Modules\Server\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Modules\Server\Models\Server;
use Modules\Server\Models\ServerPhoto;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;
use Modules\Server\Http\Requests\StoreServerRequest;
use Modules\Server\Http\Requests\UpdateServerRequest;

class ServerController extends Controller
{
    public function index()
    {
        $servers = Server::query()
            ->with(['area', 'pop'])
            ->latest()
            ->get();

        return Inertia::render('Server::Index', [
            'servers' => $servers,
        ]);
    }

    public function create()
    {
        return Inertia::render('Server::Create', [
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    public function store(StoreServerRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $server = Server::create($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $category => $files) {
                foreach ($files as $file) {
                    $path = $file->store('server-photos/' . $category, 'public');
                    $server->photos()->create([
                        'path' => $path,
                        'category' => $category,
                    ]);
                }
            }
        }

        return redirect()->route('server.index')->with('success', 'Server created successfully.');
    }

    public function show($id)
    {
        $server = Server::with(['area', 'pop', 'photos'])->findOrFail($id);

        return Inertia::render('Server::Show', [
            'server' => $server,
        ]);
    }

    public function edit($id)
    {
        $server = Server::with(['photos'])->findOrFail($id);

        return Inertia::render('Server::Edit', [
            'server' => $server,
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    public function update(UpdateServerRequest $request, $id)
    {
        $server = Server::findOrFail($id);
        $server->update($request->validated());

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $category => $files) {
                foreach ($files as $file) {
                    $path = $file->store('server-photos/' . $category, 'public');
                    $server->photos()->create([
                        'path' => $path,
                        'category' => $category,
                    ]);
                }
            }
        }

        return redirect()->route('server.index')->with('success', 'Server updated successfully.');
    }

    public function destroy($id)
    {
        $server = Server::findOrFail($id);
        $server->delete();

        return redirect()->route('server.index')->with('success', 'Server deleted successfully.');
    }
}
