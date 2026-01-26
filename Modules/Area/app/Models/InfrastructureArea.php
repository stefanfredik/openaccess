<?php

namespace Modules\Area\Models;


use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InfrastructureArea extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

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

}
