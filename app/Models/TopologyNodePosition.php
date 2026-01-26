<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopologyNodePosition extends Model
{
    protected $fillable = [
        'company_id',
        'node_uid',
        'x',
        'y',
    ];

    public function company()
    {
        return $this->belongsTo(\Modules\Company\Models\Company::class);
    }
}
