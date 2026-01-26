<?php

namespace Modules\Site\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Site\Database\Factories\SiteFactory;

class Site extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes, \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id',
        'area_id',
        'name',
        'code',
        'type',
        'latitude',
        'longitude',
        'address',
        'image',
        'description',
        'status',
        'electrical_capacity',
        'power_source',
        'has_backup_power',
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
        return $this->hasMany(SitePhoto::class);
    }
}
