<?php

namespace Modules\ActiveDevice\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DeviceService
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    private const CACHE_TTL = 300;

    /**
     * Get all devices optimized with a single UNION query instead of 6 separate queries.
     * Results are cached to avoid repeated database hits.
     *
     * @return Collection<int, array{id: int, name: string, code: string|null, type: string}>
     */
    public function getAllDevices(): Collection
    {
        return Cache::remember('all_devices_list', self::CACHE_TTL, function () {
            return $this->fetchAllDevicesFromDatabase();
        });
    }

    /**
     * Get all devices without caching (for when fresh data is needed).
     */
    public function getAllDevicesFresh(): Collection
    {
        Cache::forget('all_devices_list');

        return $this->fetchAllDevicesFromDatabase();
    }

    /**
     * Fetch all devices using optimized UNION query.
     */
    private function fetchAllDevicesFromDatabase(): Collection
    {
        $deviceTypes = [
            ['table' => 'ad_routers', 'class' => \Modules\ActiveDevice\Models\Router::class],
            ['table' => 'ad_switches', 'class' => \Modules\ActiveDevice\Models\AdSwitch::class],
            ['table' => 'ad_olts', 'class' => \Modules\ActiveDevice\Models\Olt::class],
            ['table' => 'ad_onts', 'class' => \Modules\ActiveDevice\Models\Ont::class],
            ['table' => 'ad_access_points', 'class' => \Modules\ActiveDevice\Models\AccessPoint::class],
            ['table' => 'cpes', 'class' => \Modules\Cpe\Models\Cpe::class],
        ];

        $queries = [];

        foreach ($deviceTypes as $device) {
            $queries[] = DB::table($device['table'])
                ->select([
                    'id',
                    'name',
                    'code',
                    DB::raw("'".addslashes($device['class'])."' as type"),
                ]);
        }

        // Build UNION query
        $unionQuery = array_shift($queries);
        foreach ($queries as $query) {
            $unionQuery = $unionQuery->union($query);
        }

        return $unionQuery->get()->map(fn ($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'code' => $d->code,
            'type' => $d->type,
        ]);
    }

    /**
     * Clear the devices cache.
     * Call this when devices are created, updated, or deleted.
     */
    public function clearCache(): void
    {
        Cache::forget('all_devices_list');
    }
}
