<?php

namespace Modules\Company\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory, SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('company', function ($builder) {
            if (Auth::hasUser() && Auth::user()->company_id) {
                $builder->where($builder->getModel()->getTable().'.id', Auth::user()->company_id);
            }
        });
    }

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
