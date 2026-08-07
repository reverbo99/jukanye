<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'people';

    protected $fillable = [
        'type',
        'name',
        'subtitle_en',
        'subtitle_sw',
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public static function types(): array
    {
        return [
            'speaker' => 'Speakers',
            'artist' => 'Artists',
            'hero' => 'Heroes',
            'friend' => 'Friends',
            'exhibition' => 'Exhibitions',
        ];
    }
}
