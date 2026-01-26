<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\PassiveDevice\Database\Factories\CableFactory;

class Cable extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

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
    ];

    protected $casts = [
        'path' => 'array',
        'installed_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(\Modules\Area\Models\InfrastructureArea::class, 'infrastructure_area_id');
    }
}
