<?php

namespace Modules\Server\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Server\Database\Factories\ServerFactory;

class Server extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'company_id',
        'area_id',
        'pop_id',
        'code',
        'name',
        'function',
        'building',
        'floor',
        'area_location',
        'latitude',
        'longitude',
        'description',
        'status',
    ];

    public function area()
    {
        return $this->belongsTo(\Modules\Area\Models\InfrastructureArea::class, 'area_id');
    }

    public function pop()
    {
        return $this->belongsTo(\Modules\Pop\Models\Pop::class, 'pop_id');
    }

    public function photos()
    {
        return $this->hasMany(ServerPhoto::class);
    }
}
