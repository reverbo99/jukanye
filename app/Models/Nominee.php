<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nominee extends Model
{
    protected $fillable = [
        'award_category_id',
        'name',
        'country',
        'photo',
        'bio_en',
        'bio_sw',
        'links',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'links' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AwardCategory::class, 'award_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
