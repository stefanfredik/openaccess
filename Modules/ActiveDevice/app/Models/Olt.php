<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\ActiveDevice\Database\Factories\OltFactory;

class Olt extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'olt';

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
        'username',
        'password',
        'service_status',
        'purchase_year',
        'latitude',
        'longitude',
        'pon_type',
        'is_active',
        'status',
        'installed_at',
        'description',
        'device_image',
    ];

    protected $casts = [
        'service_status' => 'array',
        'is_active' => 'boolean',
        'installed_at' => 'date:Y-m-d',
    ];

    protected $appends = ['port_count'];

    public function photos()
    {
        return $this->hasMany(OltPhoto::class);
    }

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

    public function interfaces()
    {
        return $this->morphMany(DeviceInterface::class, 'interfacable');
    }

    public function servicePorts()
    {
        return $this->morphMany(ServicePort::class, 'portable');
    }

    public function getPortCountAttribute(): int
    {
        return $this->interfaces()->count() ?: 8;
    }
}
