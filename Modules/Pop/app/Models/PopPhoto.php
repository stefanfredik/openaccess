<?php

namespace Modules\Pop\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Pop\Database\Factories\PopPhotoFactory;

class PopPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'pop_id',
        'path',
        'caption',
    ];

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }
}
