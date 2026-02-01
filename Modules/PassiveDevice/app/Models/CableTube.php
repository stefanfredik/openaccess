<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CableTube extends Model
{
    use HasFactory;

    protected $table = 'pd_cable_tubes';

    protected $fillable = [
        'cable_id',
        'color',
        'tube_number',
        'core_count',
    ];

    public function cable()
    {
        return $this->belongsTo(Cable::class, 'cable_id');
    }

    public function cores()
    {
        return $this->hasMany(CableCore::class, 'tube_id');
    }
}
