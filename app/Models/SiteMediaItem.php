<?php

namespace App\Models;

use App\Support\YoutubeUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteMediaItem extends Model
{
    public const KIND_IMAGE = 'image';

    public const KIND_YOUTUBE = 'youtube';

    /** @var array<string, string> */
    public const SLOTS = [
        'hero_slider' => 'Hero slider (Home & Splash)',
        'banner_slider' => 'Banner / partner slider',
        'featured_videos' => 'Featured YouTube videos',
        'gallery' => 'Gallery / other images',
    ];

    protected $fillable = [
        'slot',
        'kind',
        'image',
        'youtube_url',
        'title_en',
        'title_sw',
        'caption_en',
        'caption_sw',
        'link',
        'sort_order',
        'status',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeInSlot(Builder $query, string $slot): Builder
    {
        return $query->where('slot', $slot);
    }

    public function youtubeId(): ?string
    {
        return YoutubeUrl::videoId($this->youtube_url);
    }

    public function youtubeThumbnail(): ?string
    {
        $id = $this->youtubeId();

        return $id ? 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg' : null;
    }
}
