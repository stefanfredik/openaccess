<?php

namespace Modules\PassiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberSplice extends Model
{
    use HasFactory;

    protected $table = 'pd_fiber_splices';

    protected $fillable = [
        'enclosure_type',
        'enclosure_id',
        'incoming_core_id',
        'outgoing_core_id',
        'attenuation',
        'type',
    ];

    public function enclosure()
    {
        return $this->morphTo();
    }

    public function incomingCore()
    {
        return $this->belongsTo(CableCore::class, 'incoming_core_id');
    }

    public function outgoingCore()
    {
        return $this->belongsTo(CableCore::class, 'outgoing_core_id');
    }
}
