<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ActiveDevice\Database\Factories\AdSwitchFactory;

class AdSwitch extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

    protected $table = 'ad_switches';

    protected $fillable = [
        'company_id',
        'infrastructure_area_id',
        'pop_id',
        'code',
        'name',
        'brand',
        'model',
        'serial_number',
        'mac_address',
        'ip_address',
        'port_count',
        'switch_type',
        'is_active',
        'installed_at',
        'latitude',
        'longitude',
        'description',
    ];

    public function area()
    {
        return $this->belongsTo(\Modules\Area\Models\InfrastructureArea::class, 'infrastructure_area_id');
    }

    public function pop()
    {
        return $this->belongsTo(\Modules\Pop\Models\Pop::class, 'pop_id');
    }

    public function sourceConnections()
    {
        return $this->morphMany(DeviceConnection::class, 'source');
    }

    public function destinationConnections()
    {
        return $this->morphMany(DeviceConnection::class, 'destination');
    }
}
