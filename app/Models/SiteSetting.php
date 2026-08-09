<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'tagline_en',
        'tagline_sw',
        'date_label',
        'location_label',
        'festival_starts_at',
        'countdown_at',
        'donate_embed_url',
        'donate_body_en',
        'donate_body_sw',
        'about_intro_en',
        'about_intro_sw',
        'download_text_en',
        'download_text_sw',
        'footer_contact',
        'social',
        'write_locale',
        'translate_locale',
        'deepl_api_key',
        'deepl_api_plan',
    ];

    protected function casts(): array
    {
        return [
            'festival_starts_at' => 'datetime',
            'countdown_at' => 'datetime',
            'footer_contact' => 'array',
            'social' => 'array',
            'deepl_api_key' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
