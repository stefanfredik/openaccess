<?php

namespace Modules\ActiveDevice\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ActiveDevice\Database\Factories\OltPhotoFactory;

class OltPhoto extends Model
{
    use HasFactory;

    protected $table = 'ad_olt_photos';

    protected $fillable = [
        'olt_id',
        'path',
        'category',
    ];

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }
}
