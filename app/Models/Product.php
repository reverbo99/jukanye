<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name_en',
        'name_sw',
        'price',
        'currency',
        'tagline_en',
        'tagline_sw',
        'description_en',
        'description_sw',
        'image',
        'sort_order',
        'status',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
