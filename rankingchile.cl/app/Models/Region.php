<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Region extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'int',
    ];

    protected static function booted(): void
    {
        static::creating(function (Region $region) {
            if (! $region->slug) {
                $region->slug = (string) Str::slug($region->name);
            }
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class, 'region_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProfileSubmission::class, 'region_id');
    }
}
