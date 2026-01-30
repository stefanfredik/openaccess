<?php

namespace Modules\Area\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InfrastructureArea extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'type',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'description',
        'boundary',
    ];

    protected $casts = [
        'boundary' => 'array',
    ];

    public function pops()
    {
        return $this->hasMany(\Modules\Pop\Models\Pop::class, 'area_id');
    }

    public function servers()
    {
        return $this->hasMany(\Modules\Server\Models\Server::class, 'area_id');
    }
}
