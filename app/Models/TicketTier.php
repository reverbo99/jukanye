<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TicketTier extends Model
{
    protected $fillable = [
        'slug',
        'name_en',
        'name_sw',
        'price',
        'currency',
        'description_en',
        'description_sw',
        'includes',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'includes' => 'array',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public static function makeSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'tier';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
