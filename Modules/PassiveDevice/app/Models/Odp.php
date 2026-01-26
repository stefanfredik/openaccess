<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\PassiveDevice\Database\Factories\OdpFactory;

class Odp extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

    protected $table = 'pd_odps';

    protected $fillable = [
        'company_id',
        'infrastructure_area_id',
        'code',
        'name',
        'port_count',
        'used_port_count',
        'splitter_type',
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
}
