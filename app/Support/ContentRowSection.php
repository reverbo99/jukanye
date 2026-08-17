<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Shared row-card layout for news, people, tours, products, etc.
 */
class ContentRowSection
{
    /**
     * @param  array{image?: ?string, url?: ?string, meta?: ?string, title: string, body?: ?string, cta?: ?string, external?: bool, video?: bool}  $item
     */
    public static function itemHtml(array $item): string
    {
        $url = $item['url'] ?? null;
        $img = $item['image'] ?? null;
        $title = $item['title'] ?? '';
        $meta = $item['meta'] ?? null;
        $body = $item['body'] ?? null;
        $cta = $item['cta'] ?? null;
        $external = ! empty($item['external']);
        $video = ! empty($item['video']);
        $linkAttrs = $external ? ' target="_blank" rel="noopener"' : '';

        $cardClass = 'jk-row-card'.($video ? ' jk-row-card--video' : '');
        $html = '<article class="'.$cardClass.'">';
        if ($url) {
            $html .= '<a class="jk-row-card__media" href="'.e($url).'"'.$linkAttrs.' tabindex="-1" aria-hidden="true">';
        } else {
            $html .= '<div class="jk-row-card__media">';
        }
        if ($img) {
            $html .= '<img src="'.e($img).'" alt="" loading="lazy" decoding="async">';
        } else {
            $html .= '<span class="jk-row-card__placeholder" aria-hidden="true"></span>';
        }
        $html .= $url ? '</a>' : '</div>';

        $html .= '<div class="jk-row-card__body">';
        if ($meta) {
            $html .= '<div class="jk-row-card__meta">'.e($meta).'</div>';
        }
        if ($title !== '') {
            if ($url) {
                $html .= '<h3 class="jk-row-card__title"><a href="'.e($url).'"'.$linkAttrs.'>'.e($title).'</a></h3>';
            } else {
                $html .= '<h3 class="jk-row-card__title">'.e($title).'</h3>';
            }
        }
        if ($body) {
            $plain = trim(strip_tags($body));
            if ($plain !== '') {
                $html .= '<p class="jk-row-card__excerpt">'.e(Str::limit($plain, 160)).'</p>';
            }
        }
        if ($url && $cta) {
            $html .= '<a class="jk-row-card__more" href="'.e($url).'"'.$linkAttrs.'>'.e($cta).'</a>';
        }
        $html .= '</div></article>';

        return $html;
    }

    /**
     * @param  list<array{image?: ?string, url?: ?string, meta?: ?string, title: string, body?: ?string, cta?: ?string}>  $items
     */
    public static function listHtml(array $items): string
    {
        if ($items === []) {
            return '';
        }

        $html = '';
        foreach ($items as $item) {
            $html .= self::itemHtml($item);
        }

        return '<div class="jk-row-list">'.$html.'</div>';
    }

    public static function sectionHtml(
        string $heading,
        array $items,
        ?string $viewAllUrl = null,
        ?string $viewAllLabel = null,
    ): string {
        $list = self::listHtml($items);
        if ($list === '') {
            return '';
        }

        $head = '<div class="jk-section-head"><h2>'.e($heading).'</h2>';
        if ($viewAllUrl && $viewAllLabel) {
            $head .= '<a class="jk-section-head__link" href="'.e($viewAllUrl).'">'.e($viewAllLabel).'</a>';
        }
        $head .= '</div>';

        return '<section class="jk-content-section" aria-label="'.e($heading).'">'.$head.$list.'</section>';
    }
}
