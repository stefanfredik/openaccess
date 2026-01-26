<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ActiveDevice\Database\Factories\OltFactory;

class Olt extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

    protected $table = 'ad_olts';

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
        'pon_port_count',
        'uplink_port_count',
        'is_active',
        'installed_at',
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
