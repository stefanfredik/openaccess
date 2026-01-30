<?php

namespace Modules\Server\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Server\Database\Factories\RackFactory;

class Rack extends Model
{
    use \App\Traits\BelongsToCompany, HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'company_id',
        'server_id',
        'code',
        'name',
        'u_capacity',
        'width_mm',
        'depth_mm',
        'brand',
        'description',
        'status',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function contents()
    {
        return $this->hasMany(RackContent::class);
    }
}
