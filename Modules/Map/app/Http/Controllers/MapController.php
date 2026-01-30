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

    public function data(Request $request)
    {
        $areaId = $request->query('area_id');

        // 1. POPs Layer
        $popsQuery = Pop::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $popsQuery->where('area_id', $areaId);
        }
        $pops = $popsQuery->get()->map(function ($pop) {
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
        $oltsQuery = Olt::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $oltsQuery->where('infrastructure_area_id', $areaId);
        }
        $olts = $oltsQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'olt', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
            ];
        });

        $ontsQuery = Ont::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $ontsQuery->where('infrastructure_area_id', $areaId);
        }
        $onts = $ontsQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'ont', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
            ];
        });

        $routersQuery = Router::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $routersQuery->where('infrastructure_area_id', $areaId);
        }
        $routers = $routersQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'router', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
            ];
        });

        $switchesQuery = AdSwitch::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $switchesQuery->where('infrastructure_area_id', $areaId);
        }
        $switches = $switchesQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'switch', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
            ];
        });

        $apsQuery = AccessPoint::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $apsQuery->where('infrastructure_area_id', $areaId);
        }
        $aps = $apsQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'access_point', 'name' => $dev->name, 'status' => $dev->is_active ? 'active' : 'inactive'],
            ];
        });

        // 3. Passive Devices
        $odpsQuery = Odp::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $odpsQuery->where('infrastructure_area_id', $areaId);
        }
        $odps = $odpsQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'odp', 'name' => $dev->name, 'status' => $dev->status ?? 'active'],
            ];
        });

        $odfsQuery = Odf::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $odfsQuery->where('infrastructure_area_id', $areaId);
        }
        $odfs = $odfsQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'odf', 'name' => $dev->name, 'status' => $dev->status ?? 'active'],
            ];
        });

        $polesQuery = Pole::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $polesQuery->where('infrastructure_area_id', $areaId);
        }
        $poles = $polesQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'pole', 'name' => $dev->name, 'status' => $dev->status ?? 'active'],
            ];
        });

        $towersQuery = Tower::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $towersQuery->where('infrastructure_area_id', $areaId);
        }
        $towers = $towersQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'tower', 'name' => $dev->name, 'status' => $dev->status ?? 'active'],
            ];
        });

        $jbsQuery = JointBox::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $jbsQuery->where('infrastructure_area_id', $areaId);
        }
        $jointBoxes = $jbsQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'joint_box', 'name' => $dev->name, 'status' => $dev->status ?? 'active'],
            ];
        });

        // 4. CPEs
        $cpesQuery = Cpe::whereNotNull('latitude')->whereNotNull('longitude');
        if ($areaId) {
            $cpesQuery->where('infrastructure_area_id', $areaId);
        }
        $cpes = $cpesQuery->get()->map(function ($dev) {
            return [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [(float) $dev->longitude, (float) $dev->latitude]],
                'properties' => ['id' => $dev->id, 'type' => 'cpe', 'name' => $dev->customer_name, 'status' => $dev->status ?? 'active'],
            ];
        });

        // 5. Cables
        $cablesQuery = \Modules\PassiveDevice\Models\Cable::whereNotNull('path');
        if ($areaId) {
            $cablesQuery->where('infrastructure_area_id', $areaId);
        }
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
