<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\ActiveDevice\Database\Factories\DeviceInterfaceFactory;

class DeviceInterface extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory;

    protected $table = 'ad_device_interfaces';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'interfacable_id',
        'interfacable_type',
        'name',
        'type',
        'speed',
        'mac_address',
        'status',
        'description',
    ];

    public function interfacable()
    {
        return $this->morphTo();
    }

    // protected static function newFactory(): DeviceInterfaceFactory
    // {
    //     // return DeviceInterfaceFactory::new();
    // }
}
