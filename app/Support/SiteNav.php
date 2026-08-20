<?php

namespace App\Support;

/**
 * Public website navigation — main menu with sub-menus + primary action buttons.
 */
class SiteNav
{
    /**
     * @return list<array{label: string, href: string, leaf: string, external?: bool, children?: list<array{label: string, href: string, leaf: string}>}>
     */
    public static function menuTree(string $locale = 'en'): array
    {
        $isSw = $locale === 'sw';
        $prefix = $isSw ? url('/site/sw') : url('/site');

        $link = static function (
            string $labelEn,
            string $labelSw,
            string $aliasEn,
            string $aliasSw,
            string $leaf,
            bool $external = false,
        ) use ($isSw, $prefix): array {
            $alias = $isSw ? $aliasSw : $aliasEn;
            $href = $alias === '' ? rtrim($prefix, '/').'/' : $prefix.'/'.$alias;

            return [
                'label' => $isSw ? $labelSw : $labelEn,
                'href' => $href,
                'leaf' => $leaf,
                'external' => $external,
            ];
        };

        $voteUrl = trim((string) config('services.vote.url', ''));
        if ($voteUrl === '') {
            $voteUrl = url('/apk/eVoting.apk');
        }

        $tree = [
            $link('Home', 'Mwanzo', '', '', ''),
            [
                'label' => $isSw ? 'Kuhusu Sisi' : 'About Us',
                'href' => $prefix.'/'.($isSw ? 'Shughuli-Zetu' : 'About-Us'),
                'leaf' => 'about-us',
                'children' => [
                    $link('Friends', 'Marafiki', 'Friends', 'Friends', 'friends'),
                    $link('Sponsors', 'Wadhamini', 'Sponsors', 'Wadhamini', 'sponsors'),
                    $link('Festival Map', 'Ramani', 'Festival-Map', 'Festival-Map', 'festival-map'),
                    $link('Contact', 'Mawasiliano', 'Contacts', 'Mawasiliano', 'contacts'),
                ],
            ],
            [
                'label' => $isSw ? 'Ratiba' : 'Programme',
                'href' => $prefix.'/Schedule',
                'leaf' => 'schedule',
                'children' => [
                    $link('Speakers', 'Wazungumzaji', 'Speakers', 'Speakers', 'speakers'),
                    $link('Artists', 'Wasanii', 'Artists', 'Artists', 'artists'),
                    $link('Heroes', 'Mashujaa', 'Heroes', 'Heroes', 'heroes'),
                    $link('Exhibitions', 'Maonyesho', 'Exhibitions', 'Exhibitions', 'exhibitions'),
                    $link('Tourism', 'Utalii', 'Tourism', 'Tourism', 'tourism'),
                    $link('Merchandise', 'Bidhaa', 'Event-Products', 'Bidhaa-za-Tamasha', 'event-products'),
                    $link('Awards', 'Tuzo', 'Award-Nominees', 'Waliopendekezwa-kupewa-Tuzo', 'award-nominees'),
                ],
            ],
            $link('Download', 'Pakua', 'Download', 'Pakua', 'download'),
            $link('News', 'Habari', 'News', 'News', 'news'),
            [
                'label' => $isSw ? 'Kura' : 'Vote',
                'href' => $voteUrl,
                'leaf' => 'vote',
                'external' => true,
            ],
        ];

        if (self::signedIn()) {
            $tree[] = [
                'label' => 'Admin',
                'href' => url('/admin'),
                'leaf' => 'admin',
            ];
        } else {
            $tree[] = [
                'label' => $isSw ? 'Ingia' : 'Login',
                'href' => url('/login'),
                'leaf' => 'login',
            ];
        }

        return $tree;
    }

    /**
     * @return list<array{label: string, href: string, variant: string}>
     */
    public static function actionButtons(string $locale = 'en'): array
    {
        $isSw = $locale === 'sw';
        $prefix = $isSw ? url('/site/sw') : url('/site');

        return [
            [
                'label' => 'Register / Jiandikishe',
                'href' => $prefix.'/'.($isSw ? 'Jisajiri' : 'Register'),
                'variant' => 'green',
            ],
            [
                'label' => 'Buy Tickets / Tiketi',
                'href' => $prefix.'/Tickets',
                'variant' => 'gold',
            ],
            [
                'label' => 'Donors / Wafadhili',
                'href' => $prefix.'/'.($isSw ? 'Changia' : 'Donate'),
                'variant' => 'blue',
            ],
        ];
    }

    /**
     * Flat list of all navigable pages (for legacy menu injection + lookups).
     *
     * @return list<array{label: string, href: string, leaf: string}>
     */
    public static function items(string $locale = 'en'): array
    {
        $flat = [];

        $walk = static function (array $nodes) use (&$walk, &$flat): void {
            foreach ($nodes as $node) {
                if (isset($node['children'])) {
                    $flat[] = [
                        'label' => $node['label'],
                        'href' => $node['href'],
                        'leaf' => $node['leaf'],
                    ];
                    $walk($node['children']);
                } else {
                    $flat[] = [
                        'label' => $node['label'],
                        'href' => $node['href'],
                        'leaf' => $node['leaf'],
                    ];
                }
            }
        };

        $walk(self::menuTree($locale));

        return $flat;
    }

    public static function isNodeActive(array $node, string $currentLeaf): bool
    {
        if (($node['leaf'] ?? '') === $currentLeaf) {
            return true;
        }

        foreach ($node['children'] ?? [] as $child) {
            if (self::isNodeActive($child, $currentLeaf)) {
                return true;
            }
        }

        return false;
    }

    public static function renderTopNavHtml(string $locale, string $currentLeaf = ''): string
    {
        $actions = '';
        foreach (self::actionButtons($locale) as $button) {
            $actions .= '<a class="jk-top-nav__cta jk-top-nav__cta--'.e($button['variant']).'" href="'.e($button['href']).'">'
                .e($button['label']).'</a>';
        }

        $links = '';
        foreach (self::menuTree($locale) as $node) {
            $links .= self::renderNavNode($node, $currentLeaf);
        }

        $label = $locale === 'sw' ? 'Menyu kuu' : 'Main menu';

        return <<<HTML
<nav class="jk-top-nav" id="jk-top-nav" aria-label="{$label}">
    <div class="jk-top-nav__actions">{$actions}</div>
    <div class="jk-top-nav__inner">{$links}</div>
</nav>
HTML;
    }

    /**
     * @param  array{label: string, href: string, leaf: string, external?: bool, children?: list<array>}  $node
     */
    private static function renderNavNode(array $node, string $currentLeaf): string
    {
        $active = self::isNodeActive($node, $currentLeaf) ? ' is-active' : '';
        $children = $node['children'] ?? [];

        if ($children === []) {
            $extra = '';
            if (! empty($node['external'])) {
                $extra = ' target="_blank" rel="noopener noreferrer"';
            } elseif (($node['leaf'] ?? '') === 'vote' && ($node['href'] ?? '') === '#') {
                $extra = ' aria-disabled="true" onclick="return false;"';
            }

            $class = ($node['leaf'] ?? '') === 'login' ? 'jk-top-nav__link jk-top-nav__link--login' : 'jk-top-nav__link';

            return '<a class="'.$class.$active.'" href="'.e($node['href']).'"'.$extra.'>'.e($node['label']).'</a>';
        }

        $sub = '';
        foreach ($children as $child) {
            $childActive = ($child['leaf'] ?? '') === $currentLeaf ? ' is-active' : '';
            $sub .= '<a class="jk-top-nav__sublink'.$childActive.'" href="'.e($child['href']).'">'.e($child['label']).'</a>';
        }

        $label = e($node['label']);
        $href = e($node['href']);

        return '<div class="jk-top-nav__group'.$active.'">'
            .'<a class="jk-top-nav__link jk-top-nav__parent'.$active.'" href="'.$href.'">'
            .$label
            .'<svg class="jk-top-nav__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2"/></svg>'
            .'</a>'
            .'<button type="button" class="jk-top-nav__link jk-top-nav__trigger'.$active.'" aria-expanded="false">'
            .'<span>'.$label.'</span>'
            .'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2"/></svg>'
            .'</button>'
            .'<div class="jk-top-nav__submenu">'.$sub.'</div>'
            .'</div>';
    }

    public static function renderListHtml(string $locale, string $currentLeaf = ''): string
    {
        $html = '';
        foreach (self::items($locale) as $item) {
            $active = $item['leaf'] === $currentLeaf ? ' class="active wb_this_page_menu_item"' : '';
            $html .= '<li'.$active.'><a href="'.e($item['href']).'">'.e($item['label']).'</a></li>';
        }

        if (self::signedIn()) {
            $html .= '<li class="jk-nav-login"><a href="'.e(url('/admin')).'">Admin</a></li>';
        } else {
            $label = $locale === 'sw' ? 'Ingia' : 'Login';
            $html .= '<li class="jk-nav-login"><a href="'.e(url('/login')).'">'.e($label).'</a></li>';
        }

        return $html;
    }

    private static function signedIn(): bool
    {
        try {
            return auth()->check();
        } catch (\Throwable) {
            return false;
        }
    }
}
