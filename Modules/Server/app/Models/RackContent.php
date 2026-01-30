<?php

namespace Modules\Server\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Server\Database\Factories\RackContentFactory;

class RackContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'rack_id',
        'device_id',
        'device_type',
        'unit_start',
        'unit_size',
        'color',
    ];

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function device()
    {
        return $this->morphTo();
    }
}
