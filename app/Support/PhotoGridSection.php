<?php

namespace App\Support;

/**
 * Responsive photo grid for gallery / sponsor logos on the homepage.
 */
class PhotoGridSection
{
    /**
     * @param  list<array{image: string, title?: ?string, url?: ?string}>  $items
     */
    public static function gridHtml(array $items): string
    {
        if ($items === []) {
            return '';
        }

        $cells = '';
        foreach ($items as $item) {
            $title = $item['title'] ?? '';
            $alt = $title !== '' ? $title : 'Gallery';
            $inner = '<img src="'.e($item['image']).'" alt="'.e($alt).'" loading="lazy" decoding="async">';
            if (! empty($item['url'])) {
                $cells .= '<a class="jk-photo-grid__item" href="'.e($item['url']).'">'.$inner.'</a>';
            } else {
                $cells .= '<div class="jk-photo-grid__item">'.$inner.'</div>';
            }
        }

        return '<div class="jk-photo-grid">'.$cells.'</div>';
    }

    public static function sectionHtml(string $heading, array $items): string
    {
        $grid = self::gridHtml($items);
        if ($grid === '') {
            return '';
        }

        return '<section class="jk-content-section" aria-label="'.e($heading).'">'
            .'<div class="jk-section-head"><h2>'.e($heading).'</h2></div>'
            .$grid
            .'</section>';
    }
}
