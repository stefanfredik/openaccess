<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\ActiveDevice\Database\Factories\OntFactory;

class Ont extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'ad_onts';

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
        'onu_type',
        'is_active',
        'installed_at',
        'latitude',
        'longitude',
        'description',
    ];

    protected $casts = [
        'installed_at' => 'date:Y-m-d',
        'is_active' => 'boolean',
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
