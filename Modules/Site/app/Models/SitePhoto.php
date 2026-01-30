<?php

namespace Modules\Site\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Modules\Site\Database\Factories\SitePhotoFactory;

class SitePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'path',
        'caption',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
