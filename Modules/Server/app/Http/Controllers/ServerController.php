<?php

namespace Modules\Server\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;
use Modules\Server\Http\Requests\StoreServerRequest;
use Modules\Server\Http\Requests\UpdateServerRequest;
use Modules\Server\Models\Server;

class ServerController extends Controller
{
    public function index(Request $request)
    {
        $servers = Server::query()
            ->with(['area', 'pop'])
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
            ->get();

        return Inertia::render('Server::Index', [
            'servers' => $servers,
            'filters' => $request->only(['search', 'type']),
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
        $data['company_id'] = $request->user()->company_id;

        $server = Server::create($data);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $category => $files) {
                foreach ($files as $file) {
                    $path = $file->store('server-photos/'.$category, 'public');
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
        $server = Server::with([
            'area',
            'pop',
            'photos',
            'racks.contents.device.interfaces',
            'racks.contents.device.sourceConnections.sourceInterface',
            'racks.contents.device.sourceConnections.destinationInterface',
            'racks.contents.device.destinationConnections.sourceInterface',
            'racks.contents.device.destinationConnections.destinationInterface',
        ])->findOrFail($id);

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
                    $path = $file->store('server-photos/'.$category, 'public');
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
