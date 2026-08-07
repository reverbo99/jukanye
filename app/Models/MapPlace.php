<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MapPlace extends Model
{
    protected $fillable = [
        'name_en',
        'name_sw',
        'lat',
        'lng',
        'description_en',
        'description_sw',
        'sort_order',
        'status',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
