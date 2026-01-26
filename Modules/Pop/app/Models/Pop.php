<?php

namespace Modules\Pop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Pop\Database\Factories\PopFactory;

class Pop extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes, \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id',
        'area_id',
        'code',
        'name',
        'address',
        'province',
        'regency',
        'district',
        'village',
        'latitude',
        'longitude',
        'electrical_capacity',
        'power_source',
        'has_backup_power',
        'description',
        'status',
    ];

    protected $casts = [
        'has_backup_power' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(\Modules\Area\Models\InfrastructureArea::class, 'area_id');
    }

    public function photos()
    {
        return $this->hasMany(PopPhoto::class);
    }
}
