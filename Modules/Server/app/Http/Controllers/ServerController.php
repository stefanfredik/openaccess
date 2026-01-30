<?php

namespace Modules\Server\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasFlashMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Area\Models\InfrastructureArea;
use Modules\Pop\Models\Pop;
use Modules\Server\Http\Requests\StoreServerRequest;
use Modules\Server\Http\Requests\UpdateServerRequest;
use Modules\Server\Models\Server;

class ServerController extends Controller
{
    use HasFlashMessages;

    public function index(Request $request): Response
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
            ->paginate(10);

        return Inertia::render('Server::Index', [
            'servers' => $servers,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Server::Create', [
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    public function store(StoreServerRequest $request): RedirectResponse
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

        return redirect()->route('server.index')->with('success', $this->flashCreated('server'));
    }

    public function show(Server $server): Response
    {
        $server->load([
            'area',
            'pop',
            'photos',
            'racks.contents.device.interfaces',
            'racks.contents.device.sourceConnections.sourceInterface',
            'racks.contents.device.sourceConnections.destinationInterface',
            'racks.contents.device.destinationConnections.sourceInterface',
            'racks.contents.device.destinationConnections.destinationInterface',
        ]);

        return Inertia::render('Server::Show', [
            'server' => $server,
        ]);
    }

    public function edit(Server $server): Response
    {
        return Inertia::render('Server::Edit', [
            'server' => $server->load(['photos']),
            'areas' => InfrastructureArea::all(),
            'pops' => Pop::all(),
        ]);
    }

    public function update(UpdateServerRequest $request, Server $server): RedirectResponse
    {
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

        return redirect()->route('server.index')->with('success', $this->flashUpdated('server'));
    }

    public function destroy(Server $server): RedirectResponse
    {
        $server->delete();

        return redirect()->route('server.index')->with('success', $this->flashDeleted('server'));
    }
}
