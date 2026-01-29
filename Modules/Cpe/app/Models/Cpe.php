<?php

namespace Modules\Cpe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Cpe\Database\Factories\CpeFactory;

class Cpe extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

    protected $table = 'cpes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'infrastructure_area_id',
        'active_device_id',
        'active_device_type',
        'code',
        'name',
        'address',
        'longitude',
        'latitude',
        'type',
        'brand',
        'model',
        'serial_number',
        'mac_address',
        'status',
        'installed_at',
        'photo',
        'description',
    ];

    protected $casts = [
        'installed_at' => 'date:Y-m-d',
    ];

    public function area()
    {
        return $this->belongsTo(\Modules\Area\Models\InfrastructureArea::class, 'infrastructure_area_id');
    }

    public function activeDevice()
    {
        return $this->morphTo();
    }

    public function sourceConnections()
    {
        return $this->morphMany(\Modules\ActiveDevice\Models\DeviceConnection::class, 'source');
    }

    public function destinationConnections()
    {
        return $this->morphMany(\Modules\ActiveDevice\Models\DeviceConnection::class, 'destination');
    }

    // protected static function newFactory(): CpeFactory
    // {
    //     // return CpeFactory::new();
    // }
}
