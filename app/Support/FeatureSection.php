<?php

namespace App\Support;

use App\Models\HomeSection;

/**
 * Full-width feature blocks for homepage highlights (programme teasers, objectives, etc.).
 */
class FeatureSection
{
    /**
     * @param  iterable<HomeSection>  $sections
     */
    public static function listHtml(iterable $sections, string $locale): string
    {
        $items = '';
        foreach ($sections as $section) {
            $items .= self::itemHtml($section, $locale);
        }

        if ($items === '') {
            return '';
        }

        return '<div class="jk-feature-list">'.$items.'</div>';
    }

    public static function itemHtml(HomeSection $section, string $locale): string
    {
        $title = $locale === 'sw'
            ? ($section->title_sw ?: $section->title_en)
            : ($section->title_en ?: $section->title_sw);
        $body = $locale === 'sw'
            ? ($section->body_sw ?: $section->body_en)
            : ($section->body_en ?: $section->body_sw);
        $linkLabel = $locale === 'sw' ? 'Soma zaidi' : 'Learn more';

        $html = '<article class="jk-feature-card">';
        if ($title) {
            $html .= '<h3 class="jk-feature-card__title">'.e($title).'</h3>';
        }
        if ($body) {
            $html .= '<p class="jk-feature-card__body">'.nl2br(e($body)).'</p>';
        }
        if ($section->link) {
            $html .= '<a class="jk-feature-card__link" href="'.e($section->link).'">'.e($linkLabel).'</a>';
        }
        $html .= '</article>';

        return $html;
    }

    /**
     * @param  iterable<HomeSection>  $sections
     */
    public static function sectionHtml(iterable $sections, string $locale): string
    {
        $list = self::listHtml($sections, $locale);
        if ($list === '') {
            return '';
        }

        $heading = $locale === 'sw' ? 'Kuhusu Tamasha' : 'Festival highlights';

        return '<section class="jk-content-section" aria-label="'.e($heading).'">'
            .'<div class="jk-section-head"><h2>'.e($heading).'</h2></div>'
            .$list
            .'</section>';
    }
}
