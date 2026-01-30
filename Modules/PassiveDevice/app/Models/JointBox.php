<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\PassiveDevice\Database\Factories\JointBoxFactory;

class JointBox extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'pd_joint_boxes';

    protected $fillable = [
        'company_id',
        'infrastructure_area_id',
        'code',
        'name',
        'core_capacity',
        'input_count',
        'output_count',
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
