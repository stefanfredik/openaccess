<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortTermination extends Model
{
    use HasFactory;

    protected $table = 'pd_port_terminations';

    protected $fillable = [
        'port_type',
        'port_id',
        'core_id',
    ];

    public function port()
    {
        return $this->morphTo();
    }

    public function core()
    {
        return $this->belongsTo(CableCore::class, 'core_id');
    }
}
