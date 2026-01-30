<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\PassiveDevice\Database\Factories\SplitterFactory;

class Splitter extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'pd_splitters';

    protected $fillable = [
        'company_id',
        'infrastructure_area_id',
        'code',
        'name',
        'ratio',
        'brand',
        'model',
        'loss_db',
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
