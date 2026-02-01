<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\PassiveDevice\Database\Factories\CableFactory;

class Cable extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'pd_cables';

    protected $fillable = [
        'company_id',
        'infrastructure_area_id',
        'code',
        'name',
        'length',
        'core_count',
        'type',
        'brand',
        'start_point',
        'end_point',
        'longitude',
        'latitude',
        'photo',
        'description',
        'is_active',
        'installed_at',
        'path',
        'start_node_id',
        'start_node_type',
        'end_node_id',
        'end_node_type',
        'waypoint_poles',
    ];

    protected $casts = [
        'path' => 'array',
        'installed_at' => 'date',
        'is_active' => 'boolean',
        'waypoint_poles' => 'array',
    ];

    public function area()
    {
        return $this->belongsTo(\Modules\Area\Models\InfrastructureArea::class, 'infrastructure_area_id');
    }

    public function tubes()
    {
        return $this->hasMany(CableTube::class, 'cable_id');
    }

    public function cores()
    {
        return $this->hasMany(CableCore::class, 'cable_id');
    }

    public function startNode()
    {
        return $this->morphTo();
    }

    public function endNode()
    {
        return $this->morphTo();
    }
}
