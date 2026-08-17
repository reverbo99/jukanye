<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduleItem extends Model
{
    protected $fillable = [
        'starts_at',
        'ends_at',
        'title_en',
        'title_sw',
        'description_en',
        'description_sw',
        'location_en',
        'location_sw',
        'lat',
        'lng',
        'category',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function hasMapCoordinates(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
