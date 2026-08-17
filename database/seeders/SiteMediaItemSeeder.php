<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\SiteMediaItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SiteMediaItemSeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    private array $items = [
        [
            'slot' => 'hero_slider',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'hero-kilimanjaro.jpg',
            'storage_name' => 'seed-hero-kilimanjaro.jpg',
            'title_en' => 'Mount Kilimanjaro',
            'title_sw' => 'Mlima Kilimanjaro',
            'caption_en' => 'Sunrise over Kilimanjaro — the festival home backdrop.',
            'caption_sw' => 'Machweo juu ya Kilimanjaro — mandhari ya tamasha.',
            'sort_order' => 1,
        ],
        [
            'slot' => 'hero_slider',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'hero-kilimanjaro-wide.jpg',
            'storage_name' => 'seed-hero-kilimanjaro-wide.jpg',
            'title_en' => 'Kilimanjaro panorama',
            'title_sw' => 'Mandhari ya Kilimanjaro',
            'caption_en' => 'Wide festival hero image.',
            'caption_sw' => 'Picha pana ya tamasha.',
            'sort_order' => 2,
        ],
        [
            'slot' => 'hero_slider',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'hero-celebration.jpg',
            'storage_name' => 'seed-hero-celebration.jpg',
            'title_en' => 'African celebration',
            'title_sw' => 'Sherehe ya Kiafrika',
            'caption_en' => 'Culture, colour, and community on the main stage.',
            'caption_sw' => 'Utamaduni, rangi, na jamii jukwaani.',
            'sort_order' => 3,
        ],
        [
            'slot' => 'hero_slider',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'hero-festival-crowd.jpg',
            'storage_name' => 'seed-hero-festival-crowd.jpg',
            'title_en' => 'Festival crowd',
            'title_sw' => 'Umati wa tamasha',
            'caption_en' => 'Live music and energy at Jukanye Festival.',
            'caption_sw' => 'Muziki wa moja kwa moja na nguvu za Tamasha la Jukanye.',
            'sort_order' => 4,
        ],
        [
            'slot' => 'banner_slider',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'banner-concert.jpg',
            'storage_name' => 'seed-banner-concert.jpg',
            'title_en' => 'Main stage lights',
            'title_sw' => 'Taa za jukwaa kuu',
            'caption_en' => 'Partner banner — concerts and headline acts.',
            'caption_sw' => 'Bango la washirika — tamasha na wasanii wakuu.',
            'sort_order' => 1,
        ],
        [
            'slot' => 'banner_slider',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'banner-community.jpg',
            'storage_name' => 'seed-banner-community.jpg',
            'title_en' => 'Community support',
            'title_sw' => 'Msaada wa jamii',
            'caption_en' => 'Together we build Africa’s greatest festival.',
            'caption_sw' => 'Pamoja tunajenga tamasha kubwa la Afrika.',
            'sort_order' => 2,
        ],
        [
            'slot' => 'gallery',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'gallery-serengeti.jpg',
            'storage_name' => 'seed-gallery-serengeti.jpg',
            'title_en' => 'Serengeti safari',
            'title_sw' => 'Safari ya Serengeti',
            'caption_en' => 'Tourism highlight near the festival region.',
            'caption_sw' => 'Muonekano wa utalii karibu na eneo la tamasha.',
            'sort_order' => 1,
        ],
        [
            'slot' => 'gallery',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'gallery-zanzibar.jpg',
            'storage_name' => 'seed-gallery-zanzibar.jpg',
            'title_en' => 'Zanzibar coast',
            'title_sw' => 'Pwani ya Zanzibar',
            'caption_en' => 'Beach and heritage tours for visitors.',
            'caption_sw' => 'Ziara za pwani na urithi kwa wageni.',
            'sort_order' => 2,
        ],
        [
            'slot' => 'gallery',
            'kind' => SiteMediaItem::KIND_IMAGE,
            'asset' => 'gallery-heritage.jpg',
            'storage_name' => 'seed-gallery-heritage.jpg',
            'title_en' => 'Cultural heritage',
            'title_sw' => 'Urithi wa kitamaduni',
            'caption_en' => 'Honouring liberation heroes through arts and culture.',
            'caption_sw' => 'Kuwaheshimu mashujaa wa ukombozi kupitia sanaa na utamaduni.',
            'sort_order' => 3,
        ],
        [
            'slot' => 'featured_videos',
            'kind' => SiteMediaItem::KIND_YOUTUBE,
            'youtube_url' => 'https://www.youtube.com/watch?v=EoYpL3lHyf4',
            'title_en' => 'Festival highlights reel',
            'title_sw' => 'Muhtasari wa tamasha',
            'caption_en' => 'Sample featured video — replace with your official upload.',
            'caption_sw' => 'Video ya mfano — badilisha na video rasmi yako.',
            'sort_order' => 1,
        ],
        [
            'slot' => 'featured_videos',
            'kind' => SiteMediaItem::KIND_YOUTUBE,
            'youtube_url' => 'https://www.youtube.com/watch?v=RgKAFK5djSk',
            'title_en' => 'African music showcase',
            'title_sw' => 'Maonyesho ya muziki wa Afrika',
            'caption_en' => 'Featured performance slot for the app and website.',
            'caption_sw' => 'Nafasi ya maonyesho kwenye programu na tovuti.',
            'sort_order' => 2,
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('site_media_items')) {
            return;
        }

        $this->ensureStorageLink();

        foreach ($this->items as $item) {
            $attributes = [
                'slot' => $item['slot'],
                'sort_order' => $item['sort_order'],
            ];

            $values = [
                'kind' => $item['kind'],
                'title_en' => $item['title_en'],
                'title_sw' => $item['title_sw'],
                'caption_en' => $item['caption_en'] ?? null,
                'caption_sw' => $item['caption_sw'] ?? null,
                'link' => $item['link'] ?? null,
                'status' => 'published',
            ];

            if ($item['kind'] === SiteMediaItem::KIND_IMAGE) {
                $values['image'] = $this->copySeedImage(
                    $item['asset'],
                    $item['storage_name'],
                );
                $values['youtube_url'] = null;
            } else {
                $values['youtube_url'] = $item['youtube_url'];
                $values['image'] = null;
            }

            SiteMediaItem::query()->updateOrCreate($attributes, $values);
        }
    }

    private function ensureStorageLink(): void
    {
        $link = public_path('storage');
        if (file_exists($link)) {
            return;
        }

        if ($this->command !== null) {
            $this->command->call('storage:link');
        }
    }

    private function copySeedImage(string $assetFilename, string $storageFilename): string
    {
        $assetPath = database_path('seeders/assets/site-media/'.$assetFilename);
        if (! is_file($assetPath)) {
            throw new \RuntimeException("Missing seed asset: {$assetPath}");
        }

        $destPath = 'site-media/'.$storageFilename;
        $disk = Storage::disk('public');

        if (! $disk->exists($destPath)) {
            $disk->put($destPath, file_get_contents($assetPath));
        }

        Media::query()->firstOrCreate(
            ['path' => $destPath],
            [
                'disk' => 'public',
                'mime' => $this->guessMime($assetFilename),
                'alt' => $assetFilename,
            ]
        );

        return $destPath;
    }

    private function guessMime(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}
