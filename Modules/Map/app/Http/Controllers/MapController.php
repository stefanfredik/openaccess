<?php

namespace Modules\Map\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\ActiveDevice\Models\AccessPoint;
use Modules\ActiveDevice\Models\AdSwitch;
use Modules\ActiveDevice\Models\Olt;
use Modules\ActiveDevice\Models\Ont;
use Modules\ActiveDevice\Models\Router;
use Modules\Cpe\Models\Cpe;
use Modules\PassiveDevice\Models\JointBox;
use Modules\PassiveDevice\Models\Odf;
use Modules\PassiveDevice\Models\Odp;
use Modules\PassiveDevice\Models\Pole;
use Modules\PassiveDevice\Models\Tower;
use Modules\Pop\Models\Pop;

class MapController extends Controller
{
    public function index()
    {
        $areas = \Modules\Area\Models\InfrastructureArea::query()
            ->whereHas('company')
            ->get(['id', 'name', 'boundary']);

        $pops = \Modules\Pop\Models\Pop::query()
            ->get(['id', 'name']);

        return Inertia::render('Map::Index', [
            'areas' => $areas,
            'pops' => $pops,
        ]);
    }

    private function applyFilters($query, Request $request)
    {
        $areaId = $request->query('area_id');
        $minLat = $request->query('min_lat');
        $maxLat = $request->query('max_lat');
        $minLng = $request->query('min_lng');
        $maxLng = $request->query('max_lng');

        // Filter by Area if present
        if ($areaId && $areaId !== 'all') {
            // Check if model has direct area_id or infrastructure_area_id
            $table = $query->getModel()->getTable();
            if ($table === 'pops') {
                $query->where('area_id', $areaId);
            } else {
                $query->where('infrastructure_area_id', $areaId);
            }
        }

        // Filter by Bounding Box if present
        if ($minLat && $maxLat && $minLng && $maxLng) {
            $query->whereBetween('latitude', [$minLat, $maxLat])
                  ->whereBetween('longitude', [$minLng, $maxLng]);
        }
        
        // Ensure valid coordinates
        $query->whereNotNull('latitude')->whereNotNull('longitude');

        return $query;
    }

    public function data(Request $request)
    {
        // 1. POPs Layer
        $pops = $this->applyFilters(Pop::query(), $request)
            ->get(['id', 'name', 'address', 'status', 'latitude', 'longitude'])
            ->map(function ($pop) {
                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [(float) $pop->longitude, (float) $pop->latitude],
                    ],
                    'properties' => [
                        'id' => $pop->id,
                        'type' => 'pop',
                        'name' => $pop->name,
                        'address' => $pop->address,
                        'status' => $pop->status ?? 'active',
                    ],
                ];
            });

        // 2. Active Devices
        $olts = $this->applyFilters(Olt::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'olt', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        $onts = $this->applyFilters(Ont::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'ont', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        $routers = $this->applyFilters(Router::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'router', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        $switches = $this->applyFilters(AdSwitch::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'switch', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        $aps = $this->applyFilters(AccessPoint::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'access_point', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        // 3. Passive Devices
        $odps = $this->applyFilters(Odp::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'odp', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        $odfs = $this->applyFilters(Odf::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'odf', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        $poles = $this->applyFilters(Pole::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'pole', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        $towers = $this->applyFilters(Tower::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'tower', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        $jointBoxes = $this->applyFilters(JointBox::query(), $request)
            ->get(['id', 'name', 'is_active', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'joint_box', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
                ];
            });

        // 4. CPEs
        $cpes = $this->applyFilters(Cpe::query(), $request)
            ->get(['id', 'name', 'status', 'latitude', 'longitude'])
            ->map(function ($dev) {
                return [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                    'properties' => ['id' => $dev->id, 'type' => 'cpe', 'name' => $dev->name, 'status' => $dev->status ?? 'active'],
                ];
            });

        // 5. Cables (Special handling for LineString)
        $cablesQuery = \Modules\PassiveDevice\Models\Cable::whereNotNull('path');
        if ($request->area_id && $request->area_id !== 'all') {
            $cablesQuery->where('infrastructure_area_id', $request->area_id);
        }
        // TODO: Implement bounding box check for cables (complex, maybe skip for now or check start/end points)
        // For now, let's load all cables in area to avoid cutting them off
        
        $cables = $cablesQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => collect($dev->path)->map(fn ($p) => [(float) $p[1], (float) $p[0]])->toArray(), // [lng, lat]
                ],
                'properties' => [
                    'id' => $dev->id,
                    'code' => $dev->code,
                    'type' => 'cable',
                    'name' => $dev->name,
                    'status' => $dev->is_active ? 'active' : 'inactive',
                    'length' => $dev->length,
                    'type_name' => $dev->type,
                    'core_count' => $dev->core_count,
                    'brand' => $dev->brand,
                    'start_point' => $dev->start_point,
                    'end_point' => $dev->end_point,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => collect($pops)
                ->merge($olts)
                ->merge($onts)
                ->merge($routers)
                ->merge($switches)
                ->merge($aps)
                ->merge($odps)
                ->merge($odfs)
                ->merge($poles)
                ->merge($towers)
                ->merge($jointBoxes)
                ->merge($cpes)
                ->merge($cables),
        ]);
    }

    public function relocate(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $type = $request->type;
        $id = $request->id;
        $lat = $request->lat;
        $lng = $request->lng;

        $modelMap = [
            'pop' => Pop::class,
            'olt' => Olt::class,
            'ont' => Ont::class,
            'router' => Router::class,
            'switch' => AdSwitch::class,
            'access_point' => AccessPoint::class,
            'odp' => Odp::class,
            'odf' => Odf::class,
            'pole' => Pole::class,
            'tower' => Tower::class,
            'joint_box' => JointBox::class,
            'cpe' => Cpe::class,
            'cable' => \Modules\PassiveDevice\Models\Cable::class,
        ];

        if (! isset($modelMap[$type])) {
            return response()->json(['message' => 'Invalid device type'], 400);
        }

        $model = $modelMap[$type]::find($id);
        if (! $model) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        if ($type === 'cable') {
            $model->update(['path' => $request->path]);
        } else {
            $model->update([
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        }

        return response()->json(['message' => 'Position updated successfully']);
    }
}
