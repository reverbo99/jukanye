<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Str;

/**
 * Homepage and news index list — image + title row layout.
 */
class NewsSection
{
    /**
     * @param  iterable<Post>  $posts
     */
    public static function listHtml(iterable $posts, string $locale): string
    {
        $items = [];
        foreach ($posts as $post) {
            $items[] = self::itemData($post, $locale);
        }

        return ContentRowSection::listHtml($items);
    }

    /**
     * @return array{image?: ?string, url: string, meta?: string, title: string, body?: ?string, cta: string}
     */
    private static function itemData(Post $post, string $locale): array
    {
        $title = $locale === 'sw'
            ? ($post->title_sw ?: $post->title_en)
            : ($post->title_en ?: $post->title_sw);
        $excerpt = $locale === 'sw'
            ? ($post->excerpt_sw ?: $post->excerpt_en)
            : ($post->excerpt_en ?: $post->excerpt_sw);

        return [
            'image' => ApiMedia::url($post->cover_image),
            'url' => $locale === 'sw'
                ? url('/site/sw/News/'.$post->slug)
                : url('/site/News/'.$post->slug),
            'meta' => optional($post->published_at)?->format('M j, Y') ?: null,
            'title' => $title ?: ($locale === 'sw' ? 'Habari' : 'News'),
            'body' => $excerpt,
            'cta' => $locale === 'sw' ? 'Soma zaidi' : 'Read more',
        ];
    }

    public static function sectionHtml(string $locale, iterable $posts, ?string $viewAllUrl = null): string
    {
        $items = [];
        foreach ($posts as $post) {
            $items[] = self::itemData($post, $locale);
        }

        $heading = $locale === 'sw' ? 'Habari mpya' : 'Latest News';
        $viewAll = $locale === 'sw' ? 'Angalia zote' : 'View all';
        $allNews = $viewAllUrl ?? ($locale === 'sw' ? url('/site/sw/News') : url('/site/News'));

        return ContentRowSection::sectionHtml($heading, $items, $allNews, $viewAll);
    }
}
