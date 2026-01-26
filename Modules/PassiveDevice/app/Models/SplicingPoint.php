<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToCompany;
use Modules\Area\Models\InfrastructureArea;

class SplicingPoint extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'pd_splicing_points';

    protected $fillable = [
        'company_id',
        'infrastructure_area_id',
        'joint_box_id',
        'code',
        'name',
        'tray_number',
        'splicing_type',
        'status',
        'loss',
        'photo',
        'description',
        'is_active',
        'spliced_at',
    ];

    public function area()
    {
        return $this->belongsTo(InfrastructureArea::class, 'infrastructure_area_id');
    }

    public function jointBox()
    {
        return $this->belongsTo(JointBox::class, 'joint_box_id');
    }
}
