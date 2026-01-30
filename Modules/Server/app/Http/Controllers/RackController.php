<?php

namespace Modules\Server\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Server\Models\Rack;
use Modules\Server\Models\RackContent;
use Modules\Server\Models\Server;

class RackController extends Controller
{
    public function store(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:racks,code',
            'u_capacity' => 'required|integer|min:1',
            'width_mm' => 'nullable|integer',
            'depth_mm' => 'nullable|integer',
            'brand' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Maintenance,Planned',
        ]);

        $rack = $server->racks()->create(array_merge($validated, [
            'company_id' => $server->company_id,
        ]));

        return back()->with('success', 'Rack created successfully.');
    }

    public function update(Request $request, Rack $rack)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:racks,code,'.$rack->id,
            'u_capacity' => 'required|integer|min:1',
            'width_mm' => 'nullable|integer',
            'depth_mm' => 'nullable|integer',
            'brand' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Maintenance,Planned',
        ]);

        $rack->update($validated);

        return back()->with('success', 'Rack updated successfully.');
    }

    public function destroy(Rack $rack)
    {
        $rack->delete();

        return back()->with('success', 'Rack deleted successfully.');
    }

    public function mountDevice(Request $request, Rack $rack)
    {
        $validated = $request->validate([
            'device_id' => 'required|integer',
            'device_type' => 'required|string',
            'unit_start' => 'required|integer|min:1|max:'.$rack->u_capacity,
            'unit_size' => 'required|integer|min:1',
            'color' => 'nullable|string',
        ]);

        // Check if overlaps with existing contents
        $overlap = $rack->contents()->where(function ($query) use ($validated) {
            $start = $validated['unit_start'];
            $size = $validated['unit_size'];
            $end = $start - $size + 1;

            $query->where(function ($q) use ($start, $end) {
                // Check if any existing device covers these units
                $q->where('unit_start', '>=', $end)
                    ->whereRaw('unit_start - unit_size + 1 <= ?', [$start]);
            });
        })->exists();

        if ($overlap) {
            return back()->withErrors(['unit_start' => 'The selected units are already occupied.']);
        }

        $rack->contents()->create($validated);

        return back()->with('success', 'Device mounted successfully.');
    }

    public function unmountDevice(RackContent $content)
    {
        $content->delete();

        return back()->with('success', 'Device unmounted successfully.');
    }

    public function availableDevices(Server $server)
    {
        $areaId = $server->area_id;
        $popId = $server->pop_id;

        $deviceTypes = [
            'OLT' => \Modules\ActiveDevice\Models\Olt::class,
            'Router' => \Modules\ActiveDevice\Models\Router::class,
            'Switch' => \Modules\ActiveDevice\Models\AdSwitch::class,
            'ODF' => \Modules\PassiveDevice\Models\Odf::class,
        ];

        $allDevices = collect();

        foreach ($deviceTypes as $type => $modelClass) {
            $query = $modelClass::query();

            // Handle different area column names if necessary
            // Olt, Router, Switch, Odf use infrastructure_area_id
            $query->where('infrastructure_area_id', $areaId);

            // Only filter by pop_id if the column exists in the table
            $tableName = (new $modelClass)->getTable();
            $hasPopId = DB::getSchemaBuilder()->hasColumn($tableName, 'pop_id');

            if ($hasPopId && $popId) {
                $query->where('pop_id', $popId);
            }

            // Eager load interface count for active devices
            if ($type !== 'ODF') {
                $query->withCount('interfaces');
            }

            $devices = $query->get()->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $type,
                'class' => get_class($d),
                'code' => $d->code,
                'port_count' => ($d->interfaces_count > 0) ? $d->interfaces_count : ($d->port_count ?? 0),
            ]);

            $allDevices = $allDevices->concat($devices);
        }

        // Get all currently mounted devices across all racks
        $mounted = RackContent::all()->flatMap(function ($c) {
            return [
                $c->device_type.':'.$c->device_id,
                // If it's a full class name, also add the base class name just in case of inconsistencies
                (str_contains($c->device_type, '\\') ? '\\'.$c->device_type : $c->device_type).':'.$c->device_id,
            ];
        })->unique()->toArray();

        // Get all devices currently assigned to a CPE
        $inCpe = \Modules\Cpe\Models\Cpe::whereNotNull('active_device_id')
            ->whereNotNull('active_device_type')
            ->get()
            ->flatMap(function ($c) {
                return [
                    $c->active_device_type.':'.$c->active_device_id,
                    (str_contains($c->active_device_type, '\\') ? '\\'.$c->active_device_type : $c->active_device_type).':'.$c->active_device_id,
                ];
            })->unique()->toArray();

        $excluded = array_values(array_unique(array_merge($mounted, $inCpe)));

        $available = $allDevices->filter(function ($d) use ($excluded) {
            $key = $d['class'].':'.$d['id'];
            $altKey = '\\'.$d['class'].':'.$d['id'];
            return !in_array($key, $excluded) && !in_array($altKey, $excluded);
        })->values();

        return response()->json($available);
    }
}
