<?php

namespace Modules\ActiveDevice\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ActiveDevice\Database\Factories\ServicePortFactory;

use Illuminate\Database\Eloquent\Model;

class ServicePort extends Model
{
    use BelongsToCompany, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'portable_id',
        'portable_type',
        'name',
        'port',
        'status',
    ];

    public function portable()
    {
        return $this->morphTo();
    }
}
