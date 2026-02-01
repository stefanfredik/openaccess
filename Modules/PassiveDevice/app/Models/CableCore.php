<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CableCore extends Model
{
    use HasFactory;

    protected $table = 'pd_cable_cores';

    protected $fillable = [
        'cable_id',
        'tube_id',
        'color',
        'core_number',
        'status',
    ];

    public function cable()
    {
        return $this->belongsTo(Cable::class, 'cable_id');
    }

    public function tube()
    {
        return $this->belongsTo(CableTube::class, 'tube_id');
    }

    public function incomingSplices()
    {
        return $this->hasOne(FiberSplice::class, 'incoming_core_id');
    }

    public function outgoingSplices()
    {
        return $this->hasOne(FiberSplice::class, 'outgoing_core_id');
    }

    public function termination()
    {
        return $this->hasOne(PortTermination::class, 'core_id');
    }
}
