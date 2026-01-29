<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\ActiveDevice\Http\Requests\StoreDeviceConnectionRequest;
use Modules\ActiveDevice\Models\DeviceConnection;
use Modules\ActiveDevice\Models\Router;
use Modules\ActiveDevice\Models\Olt;
use Modules\ActiveDevice\Models\AdSwitch;
use App\Models\TopologyNodePosition;

class DeviceConnectionController extends Controller
{
    public function store(StoreDeviceConnectionRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        // Check if Source Port is already occupied
        $isSourceBusy = DeviceConnection::where(function ($query) use ($data) {
            $query->where('source_type', $data['source_type'])
                  ->where('source_id', $data['source_id'])
                  ->where('source_port', $data['source_port']);
        })->orWhere(function ($query) use ($data) {
            $query->where('destination_type', $data['source_type'])
                  ->where('destination_id', $data['source_id'])
                  ->where('destination_port', $data['source_port']);
        })->exists();

        if ($isSourceBusy) {
            return back()->withErrors(['source_port' => 'The selected source port is already connected to another device.']);
        }

        // Check if Destination Port is already occupied
        $isDestBusy = DeviceConnection::where(function ($query) use ($data) {
            $query->where('source_type', $data['destination_type'])
                  ->where('source_id', $data['destination_id'])
                  ->where('source_port', $data['destination_port']);
        })->orWhere(function ($query) use ($data) {
            $query->where('destination_type', $data['destination_type'])
                  ->where('destination_id', $data['destination_id'])
                  ->where('destination_port', $data['destination_port']);
        })->exists();

        if ($isDestBusy) {
            return back()->withErrors(['destination_port' => 'The selected destination port is already connected to another device.']);
        }

        DeviceConnection::create($data);

        return redirect()->back()->with('success', 'Connection created successfully.');
    }

    public function destroy($id)
    {
        $connection = DeviceConnection::findOrFail($id);
        $connection->delete();

        return redirect()->back()->with('success', 'Connection removed successfully.');
    }

    private $seenDeviceUids = [];
    private $savedPositions = [];

    public function topology()
    {
        $this->seenDeviceUids = [];
        $this->savedPositions = TopologyNodePosition::where('company_id', auth()->user()->company_id)
            ->get()
            ->pluck('y', 'node_uid')
            ->mapWithKeys(fn($y, $uid) => [$uid => ['x' => TopologyNodePosition::where('node_uid', $uid)->first()->x, 'y' => $y]])
            ->toArray();

        // Simpler way to pluck x and y
        $this->savedPositions = TopologyNodePosition::where('company_id', auth()->user()->company_id)
            ->get()
            ->keyBy('node_uid')
            ->map(fn($p) => ['x' => $p->x, 'y' => $p->y])
            ->toArray();

        // Get all connections to identify destinations correctly
        $allConnections = DeviceConnection::all(['destination_id', 'destination_type']);

        $isDestination = function ($device) use ($allConnections) {
            $class = get_class($device);
            return $allConnections->contains(function ($conn) use ($device, $class) {
                return (string) $conn->destination_id === (string) $device->id
                    && $conn->destination_type === $class;
            });
        };

        // Query potential roots from all tiers
        $routers = Router::with(['sourceConnections.destination'])->get();
        $olts = Olt::with(['sourceConnections.destination'])->get();
        $switches = AdSwitch::with(['sourceConnections.destination'])->get();
        $onts = \Modules\ActiveDevice\Models\Ont::with(['sourceConnections.destination'])->get();
        $aps = \Modules\ActiveDevice\Models\AccessPoint::with(['sourceConnections.destination'])->get();
        $cpes = \Modules\Cpe\Models\Cpe::with(['sourceConnections.destination'])->get();

        $allDevices = collect()
            ->merge($routers)
            ->merge($olts)
            ->merge($switches)
            ->merge($onts)
            ->merge($aps)
            ->merge($cpes);

        $rootDevices = $allDevices->reject($isDestination);

        // If no clear roots, fallback to routers
        if ($rootDevices->isEmpty() && $routers->isNotEmpty()) {
            $rootDevices = $routers;
        }

        $topology = $rootDevices->map(function ($device) {
            return $this->mapDeviceToNode($device);
        });

        return Inertia::render('ActiveDevice::Topology', [
            'topology' => $topology->filter()->values()
        ]);
    }

    private function mapDeviceToNode($device, $connection = null)
    {
        if (!$device)
            return null;

        $uid = get_class($device) . '-' . $device->id;
        $isDuplicate = in_array($uid, $this->seenDeviceUids);

        if (!$isDuplicate) {
            $this->seenDeviceUids[] = $uid;
        }

        return [
            'id' => $device->id,
            'uid' => $uid,
            'name' => $device->name,
            'code' => $device->code,
            'type' => get_class($device),
            'status' => $device->status ?? ($device->is_active ? 'Active' : 'Inactive'),
            'ip_address' => $device->ip_address,
            'connection_type' => $connection ? $connection->connection_type : null,
            'source_port' => $connection ? ($connection->sourceInterface ? $connection->sourceInterface->name : $connection->source_port) : null,
            'destination_port' => $connection ? ($connection->destinationInterface ? $connection->destinationInterface->name : $connection->destination_port) : null,
            'port_name' => $connection ? $connection->port_name : null,
            'is_duplicate' => $isDuplicate,
            'x' => $this->savedPositions[$uid]['x'] ?? null,
            'y' => $this->savedPositions[$uid]['y'] ?? null,
            'children' => (!$isDuplicate && method_exists($device, 'sourceConnections'))
                ? $device->sourceConnections()->with('destination')->get()->map(function ($conn) {
                    return $this->mapDeviceToNode($conn->destination, $conn);
                })->filter()->values() : []
        ];
    }

    public function updatePositions(Request $request)
    {
        $positions = $request->input('positions', []);
        $companyId = auth()->user()->company_id;

        foreach ($positions as $uid => $pos) {
            TopologyNodePosition::updateOrCreate(
                ['node_uid' => $uid, 'company_id' => $companyId],
                ['x' => $pos['x'], 'y' => $pos['y']]
            );
        }

        return redirect()->back()->with('success', 'Layout saved successfully.');
    }

    public function details(Request $request)
    {
        $uid = $request->input('uid');
        if (!$uid) {
            return response()->json(['error' => 'UID required'], 400);
        }

        // Parse UID: "Namespace\Class-ID" or "Class-ID"
        // UID format created in mapDeviceToNode: get_class($device) . '-' . $device->id
        // Example: Modules\ActiveDevice\Models\Router-1

        $parts = explode('-', $uid);
        $id = array_pop($parts);
        $modelClass = implode('-', $parts); // Rejoin in case class name had hyphens? (unlikely but safe)

        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Invalid model class'], 400);
        }

        $device = $modelClass::with([
            'area',
            'pop',
            'sourceConnections.destination',
            'destinationConnections.source'
        ])->find($id);

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // Map connections to unified format
        $connections = collect();

        // Outgoing connections (Source = This Device)
        if ($device->sourceConnections) {
            foreach ($device->sourceConnections as $conn) {
                if ($conn->destination) {
                    $connections->push([
                        'id' => $conn->id,
                        'direction' => 'out',
                        'local_port' => $conn->sourceInterface ? $conn->sourceInterface->name : $conn->source_port,
                        'remote_device' => $conn->destination->name,
                        'remote_port' => $conn->destinationInterface ? $conn->destinationInterface->name : $conn->destination_port,
                        'type' => $conn->connection_type,
                    ]);
                }
            }
        }

        // Incoming connections (Destination = This Device)
        if ($device->destinationConnections) {
            foreach ($device->destinationConnections as $conn) {
                if ($conn->source) {
                    $connections->push([
                        'id' => $conn->id,
                        'direction' => 'in',
                        'local_port' => $conn->destinationInterface ? $conn->destinationInterface->name : $conn->destination_port,
                        'remote_device' => $conn->source->name,
                        'remote_port' => $conn->sourceInterface ? $conn->sourceInterface->name : $conn->source_port,
                        'type' => $conn->connection_type,
                    ]);
                }
            }
        }

        return response()->json([
            'id' => $device->id,
            'uid' => $uid,
            'name' => $device->name,
            'code' => $device->code,
            'type' => class_basename($modelClass),
            'status' => $device->status ?? ($device->is_active ? 'Active' : 'Inactive'),
            'ip_address' => $device->ip_address,
            'mac_address' => $device->mac_address ?? null,
            'serial_number' => $device->serial_number ?? null,
            'brand' => $device->brand ?? null,
            'model' => $device->model ?? null,
            'port_count' => $device->port_count ?? null,
            'description' => $device->description ?? null,
            'installed_at' => $device->installed_at ?? null,
            'area_name' => $device->area ? $device->area->name : null,
            'pop_name' => $device->pop ? $device->pop->name : null,
            'connections' => $connections->values(),
        ]);
    }
}
