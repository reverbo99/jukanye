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
            ?string $hrefOverride = null,
        ) use ($isSw, $prefix): array {
            if ($hrefOverride !== null) {
                return [
                    'label' => $isSw ? $labelSw : $labelEn,
                    'href' => $hrefOverride,
                    'leaf' => $leaf,
                    'external' => $external,
                ];
            }

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

        return [
            $link('Home', 'Mwanzo', '', '', ''),
            [
                'label' => $isSw ? 'Kuhusu Sisi' : 'About Us',
                'href' => $prefix.'/'.($isSw ? 'Shughuli-Zetu' : 'About-Us'),
                'leaf' => 'about-us',
                'children' => [
                    $link('Download', 'Pakua', 'Download', 'Pakua', 'download'),
                    $link('Contact', 'Mawasiliano', 'Contacts', 'Mawasiliano', 'contacts'),
                ],
            ],
            [
                'label' => $isSw ? 'Gundua' : 'Explore',
                'href' => $prefix.'/Schedule',
                'leaf' => 'explore',
                'children' => [
                    $link('Programme', 'Ratiba', 'Schedule', 'Schedule', 'schedule'),
                    $link('Speakers', 'Wazungumzaji', 'Speakers', 'Speakers', 'speakers'),
                    $link('Artists', 'Wasanii', 'Artists', 'Artists', 'artists'),
                    $link('Heroes', 'Mashujaa', 'Heroes', 'Heroes', 'heroes'),
                    $link('Exhibitions', 'Maonyesho', 'Exhibitions', 'Exhibitions', 'exhibitions'),
                    $link('Awards', 'Tuzo', 'Award-Nominees', 'Waliopendekezwa-kupewa-Tuzo', 'award-nominees'),
                    $link('Festival Map', 'Ramani', 'Festival-Map', 'Festival-Map', 'festival-map'),
                ],
            ],
            [
                'label' => $isSw ? 'Uzoefu' : 'Experience',
                'href' => $prefix.'/Tourism',
                'leaf' => 'experience',
                'children' => [
                    $link('Tourism', 'Utalii', 'Tourism', 'Tourism', 'tourism'),
                    $link('Food', 'Chakula', 'About-Us', 'Shughuli-Zetu', 'food'),
                    $link('Merchandise', 'Bidhaa', 'Event-Products', 'Bidhaa-za-Tamasha', 'event-products'),
                ],
            ],
            [
                'label' => $isSw ? 'Jamii' : 'Community',
                'href' => $prefix.'/News',
                'leaf' => 'community',
                'children' => [
                    $link('News', 'Habari', 'News', 'News', 'news'),
                    $link('Friends', 'Marafiki', 'Friends', 'Friends', 'friends'),
                    $link('Sponsors', 'Wadhamini', 'Sponsors', 'Wadhamini', 'sponsors'),
                ],
            ],
            [
                'label' => $isSw ? 'Kura' : 'Vote',
                'href' => $voteUrl,
                'leaf' => 'vote',
                'external' => true,
            ],
            [
                'label' => $isSw ? 'Akaunti' : 'Account',
                'href' => self::signedIn() ? url('/profile') : url('/login'),
                'leaf' => 'account',
                'children' => self::accountChildren($locale, $link),
            ],
        ];
    }

    /**
     * @return list<array{label: string, href: string, leaf: string, external?: bool}>
     */
    private static function accountChildren(string $locale, callable $link): array
    {
        $isSw = $locale === 'sw';
        $prefix = $isSw ? url('/site/sw') : url('/site');

        $settingsHref = self::signedIn() ? url('/profile') : url('/login');
        $children = [
            [
                'label' => $isSw ? 'Mipangilio' : 'Settings',
                'href' => $settingsHref,
                'leaf' => 'settings',
            ],
        ];

        if (self::signedIn()) {
            $children[] = [
                'label' => $isSw ? 'Wasifu' : 'Profile',
                'href' => url('/profile'),
                'leaf' => 'profile',
            ];

            if (self::isAdmin()) {
                $children[] = [
                    'label' => 'Admin',
                    'href' => url('/admin'),
                    'leaf' => 'admin',
                ];
            }
        } else {
            $children[] = [
                'label' => $isSw ? 'Ingia / Jisajili' : 'Login / Sign up',
                'href' => url('/login'),
                'leaf' => 'login',
            ];
            $children[] = $link('Register', 'Jisajili', 'Register', 'Jisajiri', 'register');
        }

        return $children;
    }

    /**
     * @return list<array{label: string, href: string, variant: string, external?: bool}>
     */
    public static function actionButtons(string $locale = 'en'): array
    {
        $isSw = $locale === 'sw';
        $prefix = $isSw ? url('/site/sw') : url('/site');

        $voteUrl = trim((string) config('services.vote.url', ''));
        if ($voteUrl === '') {
            $voteUrl = url('/apk/eVoting.apk');
        }

        return [
            [
                'label' => $isSw ? 'Shughuli' : 'Activities',
                'href' => $prefix.'/'.($isSw ? 'Shughuli-Zetu' : 'About-Us'),
                'variant' => 'green',
            ],
            [
                'label' => $isSw ? 'Tiketi' : 'Buy Tickets',
                'href' => $prefix.'/Tickets',
                'variant' => 'gold',
            ],
            [
                'label' => $isSw ? 'Changia' : 'Support',
                'href' => $prefix.'/'.($isSw ? 'Changia' : 'Donate'),
                'variant' => 'green',
            ],
            [
                'label' => $isSw ? 'Kura' : 'Vote',
                'href' => $voteUrl,
                'variant' => 'dark',
                'external' => true,
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
        $links = '';
        foreach (self::menuTree($locale) as $node) {
            $links .= self::renderNavNode($node, $currentLeaf);
        }

        $label = $locale === 'sw' ? 'Menyu kuu' : 'Main menu';

        return <<<HTML
<nav class="jk-top-nav" id="jk-top-nav" aria-label="{$label}">
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

            $class = in_array(($node['leaf'] ?? ''), ['login', 'account'], true)
                ? 'jk-top-nav__link jk-top-nav__link--login'
                : 'jk-top-nav__link';

            return '<a class="'.$class.$active.'" href="'.e($node['href']).'"'.$extra.'>'.e($node['label']).'</a>';
        }

        $sub = '';
        foreach ($children as $child) {
            $childActive = ($child['leaf'] ?? '') === $currentLeaf ? ' is-active' : '';
            $childExternal = ! empty($child['external']) ? ' target="_blank" rel="noopener noreferrer"' : '';
            $sub .= '<a class="jk-top-nav__sublink'.$childActive.'" href="'.e($child['href']).'"'.$childExternal.'>'.e($child['label']).'</a>';
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

    private static function isAdmin(): bool
    {
        try {
            $user = auth()->user();

            return $user !== null && (bool) ($user->is_admin ?? false);
        } catch (\Throwable) {
            return false;
        }
    }
}
