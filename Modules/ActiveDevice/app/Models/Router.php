<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\ActiveDevice\Database\Factories\RouterFactory;

class Router extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'ad_routers';

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
        'is_active',
        'installed_at',
        'latitude',
        'longitude',
        'description',
        'username',
        'password',
        'purchase_year',
        'photo',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'installed_at' => 'date:Y-m-d',
        'is_active' => 'boolean',
        'purchase_year' => 'integer',
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

    public function servicePorts()
    {
        return $this->morphMany(ServicePort::class, 'portable');
    }

    public function interfaces()
    {
        return $this->morphMany(DeviceInterface::class, 'interfacable');
    }
}
