<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ActiveDevice\Database\Factories\DeviceConnectionFactory;

class DeviceConnection extends Model
{
    use HasFactory, \App\Traits\BelongsToCompany;

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

    public function source()
    {
        return $this->morphTo();
    }

    public function destination()
    {
        return $this->morphTo();
    }
}
