<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\ActiveDevice\Database\Factories\DeviceConnectionFactory;

class DeviceConnection extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'ad_device_connections';

    protected $fillable = [
        'company_id',
        'source_id',
        'source_type',
        'destination_id',
        'destination_type',
        'connection_type',
        'source_port',
        'destination_port',
        'port_name',
        'description',
    ];

    public function sourceInterface()
    {
        return $this->belongsTo(DeviceInterface::class, 'source_port');
    }

    public function destinationInterface()
    {
        return $this->belongsTo(DeviceInterface::class, 'destination_port');
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function destination()
    {
        return $this->morphTo();
    }
}
