<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProfileCategory extends Model
{
    protected $table = 'profile_categories';

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'description',
        'hero_title',
        'hero_description',
        'seo_title',
        'seo_description',
        'seo_content',
        'icon',
        'is_active',
        'show_in_navigation',
        'is_indexable',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_navigation' => 'boolean',
        'is_indexable' => 'boolean',
        'sort_order' => 'int',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProfileCategory $category) {
            if (! $category->slug) {
                $category->slug = (string) Str::slug($category->name);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeNavigation(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('show_in_navigation', true)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeIndexable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('is_indexable', true);
    }

    public function displayName(): string
    {
        return $this->short_name ?: $this->name;
    }

    public function heroTitleFallback(): string
    {
        return '¿Quién manda en '.$this->displayName().' esta semana?';
    }

    public function heroDescriptionFallback(): string
    {
        return 'Ranking semanal de '.$this->displayName().' en Chile. Mira quién va primero, cuánto cuesta subir de posición y entra a competir en Quién Manda.';
    }

    public function seoTitleGenerated(): string
    {
        return 'Quién manda en '.$this->displayName().' esta semana | Quién Manda';
    }

    public function seoDescriptionGenerated(): string
    {
        return $this->heroDescriptionFallback();
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class, 'profile_category_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProfileSubmission::class, 'profile_category_id');
    }
}
