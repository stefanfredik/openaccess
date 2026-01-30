<?php

namespace Modules\Server\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Server\Database\Factories\ServerPhotoFactory;

class ServerPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'path',
        'category',
        'caption',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
