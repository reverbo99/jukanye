<?php

namespace App\Support;

/**
 * Public website navigation aligned with the Flutter app sidebar
 * (jukanye-app/lib/data/app_data.dart menuItems + Tickets).
 */
class SiteNav
{
    /**
     * @return list<array{label: string, href: string, leaf: string}>
     */
    public static function items(string $locale = 'en'): array
    {
        $isSw = $locale === 'sw';
        $prefix = $isSw ? url('/site/sw') : url('/site');

        $map = $isSw
            ? [
                ['Mwanzo', '', ''],
                ['Kuhusu', 'Shughuli-Zetu', 'about-us'],
                ['Ratiba', 'Schedule', 'schedule'],
                ['Wazungumzaji', 'Speakers', 'speakers'],
                ['Wasanii', 'Artists', 'artists'],
                ['Mashujaa', 'Heroes', 'heroes'],
                ['Maonyesho', 'Exhibitions', 'exhibitions'],
                ['Utalii', 'Tourism', 'tourism'],
                ['Bidhaa', 'Bidhaa-za-Tamasha', 'event-products'],
                ['Marafiki', 'Friends', 'friends'],
                ['Tiketi', 'Tickets', 'tickets'],
                ['Changia', 'Changia', 'donate'],
                ['Tuzo', 'Waliopendekezwa-kupewa-Tuzo', 'award-nominees'],
                ['Wadhamini', 'Wadhamini', 'sponsors'],
                ['Habari', 'News', 'news'],
                ['Ramani', 'Festival-Map', 'festival-map'],
                ['Mawasiliano', 'Mawasiliano', 'contacts'],
                ['Pakua', 'Pakua', 'download'],
                ['Jisajiri', 'Jisajiri', 'register'],
            ]
            : [
                ['Home', '', ''],
                ['About', 'About-Us', 'about-us'],
                ['Programme', 'Schedule', 'schedule'],
                ['Speakers', 'Speakers', 'speakers'],
                ['Artists', 'Artists', 'artists'],
                ['Heroes', 'Heroes', 'heroes'],
                ['Exhibitions', 'Exhibitions', 'exhibitions'],
                ['Tourism', 'Tourism', 'tourism'],
                ['Merchandise', 'Event-Products', 'event-products'],
                ['Friends', 'Friends', 'friends'],
                ['Tickets', 'Tickets', 'tickets'],
                ['Donate', 'Donate', 'donate'],
                ['Awards', 'Award-Nominees', 'award-nominees'],
                ['Sponsors', 'Sponsors', 'sponsors'],
                ['News', 'News', 'news'],
                ['Festival Map', 'Festival-Map', 'festival-map'],
                ['Contact', 'Contacts', 'contacts'],
                ['Download', 'Download', 'download'],
                ['Register', 'Register', 'register'],
            ];

        return array_map(static function (array $row) use ($prefix) {
            [$label, $alias, $leaf] = $row;
            $href = $alias === '' ? rtrim($prefix, '/').'/' : $prefix.'/'.$alias;

            return [
                'label' => $label,
                'href' => $href,
                'leaf' => $leaf,
            ];
        }, $map);
    }

    public static function renderListHtml(string $locale, string $currentLeaf = ''): string
    {
        $html = '';
        foreach (self::items($locale) as $item) {
            $active = $item['leaf'] === $currentLeaf ? ' class="active wb_this_page_menu_item"' : '';
            $html .= '<li'.$active.'><a href="'.e($item['href']).'">'.e($item['label']).'</a></li>';
        }

        $signedIn = false;
        try {
            $signedIn = auth()->check();
        } catch (\Throwable) {
            $signedIn = false;
        }

        if ($signedIn) {
            $html .= '<li class="jk-nav-login"><a href="'.e(url('/admin')).'">Admin</a></li>';
        } else {
            $label = $locale === 'sw' ? 'Ingia' : 'Login';
            $html .= '<li class="jk-nav-login"><a href="'.e(url('/login')).'">'.e($label).'</a></li>';
        }

        return $html;
    }
}
