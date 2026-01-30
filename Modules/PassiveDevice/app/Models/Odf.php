<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\PassiveDevice\Database\Factories\OdfFactory;

class Odf extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'pd_odfs';

    protected $fillable = [
        'company_id',
        'infrastructure_area_id',
        'code',
        'name',
        'port_count',
        'used_port_count',
        'core_capacity',
        'brand',
        'model',
        'longitude',
        'latitude',
        'photo',
        'description',
        'is_active',
        'installed_at',
    ];

    public function area()
    {
        return $this->belongsTo(\Modules\Area\Models\InfrastructureArea::class, 'infrastructure_area_id');
    }

    public function sourceConnections()
    {
        return $this->morphMany(\Modules\ActiveDevice\Models\DeviceConnection::class, 'source');
    }

    public function destinationConnections()
    {
        return $this->morphMany(\Modules\ActiveDevice\Models\DeviceConnection::class, 'destination');
    }
}
