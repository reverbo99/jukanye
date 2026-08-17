<?php

namespace App\Support;

/**
 * Public website shell aligned with the Flutter app UI (dark theme, gold/green CTAs,
 * Cinzel + DM Sans, bottom nav, drawer menu).
 */
class SiteTheme
{
    /** @var array<string, int> */
    private const TAB_LEAVES = [
        '' => 0,
        'homeb' => 0,
        'mwanzo' => 0,
        'tickets' => 1,
        'tiketi' => 1,
        'donate' => 2,
        'changia' => 2,
        'about-us' => 3,
        'shughuli-zetu' => 3,
    ];

    public static function apply(string $html, string $locale, string $currentLeaf = ''): string
    {
        $html = self::injectHead($html);
        $html = self::injectBodyClass($html, $currentLeaf);
        $html = self::injectAppBar($html, $locale, $currentLeaf);
        $html = self::injectShell($html, $locale, $currentLeaf);

        return $html;
    }

    public static function panelCss(): string
    {
        return '';
    }

    public static function cssUrl(): string
    {
        return asset('site/css/jukanye-app-theme.css');
    }

    public static function fontsLink(): string
    {
        return '<link rel="preconnect" href="https://fonts.googleapis.com">'
            .'<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
            .'<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">';
    }

    public static function tabIndex(string $leaf): int
    {
        $normalized = strtolower(trim($leaf, '/'));

        return self::TAB_LEAVES[$normalized] ?? -1;
    }

    public static function pageTitle(string $locale, string $leaf): string
    {
        $isSw = $locale === 'sw';
        $map = $isSw
            ? [
                '' => 'Mwanzo',
                'about-us' => 'Kuhusu',
                'schedule' => 'Ratiba',
                'tickets' => 'Tiketi',
                'donate' => 'Changia',
                'news' => 'Habari',
                'tourism' => 'Utalii',
                'festival-map' => 'Ramani',
                'event-products' => 'Bidhaa',
                'contacts' => 'Mawasiliano',
            ]
            : [
                '' => 'Home',
                'about-us' => 'About',
                'schedule' => 'Programme',
                'tickets' => 'Tickets',
                'donate' => 'Donate',
                'news' => 'News',
                'tourism' => 'Tourism',
                'festival-map' => 'Festival Map',
                'event-products' => 'Merchandise',
                'contacts' => 'Contact',
            ];

        return $map[strtolower($leaf)] ?? ($isSw ? 'Jukanye' : 'Jukanye');
    }

    private static function injectHead(string $html): string
    {
        $assets = self::fontsLink()
            .'<link rel="stylesheet" href="'.e(self::cssUrl()).'?v=8">';

        if (stripos($html, '</head>') !== false) {
            return str_ireplace('</head>', $assets.'</head>', $html);
        }

        return $assets.$html;
    }

    private static function injectBodyClass(string $html, string $currentLeaf = ''): string
    {
        $extra = $currentLeaf === '' ? ' jk-home-page' : ' jk-cms-page';

        if (preg_match('/<body([^>]*)>/i', $html, $m)) {
            $attrs = $m[1];
            $pageClass = $currentLeaf === '' ? 'jk-home-page' : 'jk-cms-page';

            if (stripos($attrs, 'jk-app-site') !== false) {
                if (stripos($attrs, $pageClass) === false) {
                    $replacement = stripos($attrs, 'class=') !== false
                        ? preg_replace('/class="([^"]*)"/i', 'class="$1 '.$pageClass.'"', $m[0], 1)
                        : '<body'.$attrs.' class="'.$pageClass.'">';

                    return preg_replace('/<body[^>]*>/i', (string) $replacement, $html, 1) ?? $html;
                }

                return $html;
            }
            $replacement = stripos($attrs, 'class=') !== false
                ? preg_replace('/class="([^"]*)"/i', 'class="$1 jk-app-site'.$extra.'"', $m[0], 1)
                : '<body'.$attrs.' class="jk-app-site'.$extra.'">';

            return preg_replace('/<body[^>]*>/i', (string) $replacement, $html, 1) ?? $html;
        }

        return $html;
    }

    private static function injectAppBar(string $html, string $locale, string $currentLeaf): string
    {
        $bar = self::appBarHtml($locale, $currentLeaf)
            .self::topNavHtml($locale, $currentLeaf);

        if (preg_match('/<body[^>]*>/i', $html, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1] + strlen($m[0][0]);

            return substr($html, 0, $pos).$bar.substr($html, $pos);
        }

        return $bar.$html;
    }

    private static function injectShell(string $html, string $locale, string $currentLeaf): string
    {
        $shell = self::shellScript();

        if (stripos($html, '</body>') !== false) {
            return str_ireplace('</body>', $shell.'</body>', $html);
        }

        return $html.$shell;
    }

    public static function appBarHtml(string $locale, string $currentLeaf): string
    {
        $isSw = $locale === 'sw';
        $homeUrl = $isSw ? url('/site/sw') : url('/site');
        $title = e(self::pageTitle($locale, $currentLeaf));
        $enUrl = e(self::langUrl('en', $currentLeaf));
        $swUrl = e(self::langUrl('sw', $currentLeaf));
        $enActive = $locale === 'en' ? ' is-active' : '';
        $swActive = $locale === 'sw' ? ' is-active' : '';

        return <<<HTML
<div class="jk-app-bar" role="banner">
    <div class="jk-app-bar__left">
        <button type="button" class="jk-app-bar__menu-btn jk-nav-toggle" id="jk-nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="jk-top-nav">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
        </button>
        <a class="jk-app-bar__brand jk-app-bar__brand--mobile" href="{$homeUrl}">JUKANYE</a>
        <span class="jk-app-bar__title">{$title}</span>
    </div>
    <a class="jk-app-bar__brand jk-app-bar__brand--desktop" href="{$homeUrl}">JUKANYE</a>
    <div class="jk-app-bar__langs">
        <a href="{$enUrl}" class="{$enActive}">EN</a>
        <a href="{$swUrl}" class="{$swActive}">SW</a>
    </div>
</div>
HTML;
    }

    public static function topNavHtml(string $locale, string $currentLeaf): string
    {
        $isSw = $locale === 'sw';
        $label = $isSw ? 'Menyu kuu' : 'Main menu';

        $links = '';
        foreach (SiteNav::items($locale) as $item) {
            $active = $item['leaf'] === $currentLeaf ? ' is-active' : '';
            $links .= '<a class="jk-top-nav__link'.$active.'" href="'.e($item['href']).'">'.e($item['label']).'</a>';
        }

        $signedIn = false;
        try {
            $signedIn = auth()->check();
        } catch (\Throwable) {
            $signedIn = false;
        }

        if ($signedIn) {
            $links .= '<a class="jk-top-nav__link jk-top-nav__link--login" href="'.e(url('/admin')).'">Admin</a>';
        } else {
            $loginLabel = $isSw ? 'Ingia' : 'Login';
            $links .= '<a class="jk-top-nav__link jk-top-nav__link--login" href="'.e(url('/login')).'">'.e($loginLabel).'</a>';
        }

        return <<<HTML
<nav class="jk-top-nav" id="jk-top-nav" aria-label="{$label}">
    <div class="jk-top-nav__inner">{$links}</div>
</nav>
HTML;
    }

    public static function bottomNavHtml(string $locale, string $currentLeaf): string
    {
        $isSw = $locale === 'sw';
        $prefix = $isSw ? url('/site/sw') : url('/site');
        $active = self::tabIndex($currentLeaf);

        $tabs = [
            [
                'href' => rtrim($prefix, '/').'/',
                'label' => $isSw ? 'Mwanzo' : 'Home',
                'icon' => '<path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z"/>',
            ],
            [
                'href' => $prefix.'/Tickets',
                'label' => $isSw ? 'Tiketi' : 'Tickets',
                'icon' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H4V8Zm0 3h16v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5Zm4 2.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/>',
            ],
            [
                'href' => $prefix.'/'.($isSw ? 'Changia' : 'Donate'),
                'label' => $isSw ? 'Changia' : 'Donate',
                'icon' => '<path d="M12 20.5s-7-4.35-7-9.2C5 8.02 7.02 6 9.5 6c1.54 0 2.98.78 3.82 2.08C14.16 6.78 15.6 6 17.14 6 19.62 6 21.64 8.02 21.64 11.3c0 4.85-7 9.2-7 9.2Z"/>',
            ],
            [
                'href' => $prefix.'/'.($isSw ? 'Shughuli-Zetu' : 'About-Us'),
                'label' => $isSw ? 'Kuhusu' : 'About',
                'icon' => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 5a1.25 1.25 0 1 1 0 2.5A1.25 1.25 0 0 1 12 7Zm-1 4h2v8h-2v-8Z"/>',
            ],
        ];

        $links = '';
        foreach ($tabs as $i => $tab) {
            $class = $active === $i ? ' is-active' : '';
            $links .= '<a class="jk-bottom-nav__item'.$class.'" href="'.e($tab['href']).'">'
                .'<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'.$tab['icon'].'</svg>'
                .'<span>'.e($tab['label']).'</span></a>';
        }

        return <<<HTML
<nav class="jk-bottom-nav" aria-label="Primary">
    <div class="jk-bottom-nav__inner">{$links}</div>
</nav>
HTML;
    }

    private static function shellScript(): string
    {
        return <<<'HTML'
<script>
(function () {
    var nav = document.getElementById('jk-top-nav');
    var toggle = document.getElementById('jk-nav-toggle');
    if (!nav || !toggle) return;

    function setOpen(open) {
        nav.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('jk-nav-open', open);
    }

    toggle.addEventListener('click', function () {
        setOpen(!nav.classList.contains('is-open'));
    });

    nav.addEventListener('click', function (e) {
        if (e.target.closest('a')) setOpen(false);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setOpen(false);
    });

    window.matchMedia('(min-width: 900px)').addEventListener('change', function (e) {
        if (e.matches) setOpen(false);
    });
})();
</script>
HTML;
    }

    private static function langUrl(string $locale, string $leaf): string
    {
        $map = [
            '' => ['en' => '', 'sw' => 'sw'],
            'about-us' => ['en' => 'About-Us', 'sw' => 'sw/Shughuli-Zetu'],
            'schedule' => ['en' => 'Schedule', 'sw' => 'sw/Schedule'],
            'tickets' => ['en' => 'Tickets', 'sw' => 'sw/Tickets'],
            'donate' => ['en' => 'Donate', 'sw' => 'sw/Changia'],
            'news' => ['en' => 'News', 'sw' => 'sw/News'],
            'tourism' => ['en' => 'Tourism', 'sw' => 'sw/Tourism'],
            'festival-map' => ['en' => 'Festival-Map', 'sw' => 'sw/Festival-Map'],
            'event-products' => ['en' => 'Event-Products', 'sw' => 'sw/Bidhaa-za-Tamasha'],
            'contacts' => ['en' => 'Contacts', 'sw' => 'sw/Mawasiliano'],
            'speakers' => ['en' => 'Speakers', 'sw' => 'sw/Speakers'],
            'artists' => ['en' => 'Artists', 'sw' => 'sw/Artists'],
            'heroes' => ['en' => 'Heroes', 'sw' => 'sw/Heroes'],
            'exhibitions' => ['en' => 'Exhibitions', 'sw' => 'sw/Exhibitions'],
            'friends' => ['en' => 'Friends', 'sw' => 'sw/Friends'],
            'award-nominees' => ['en' => 'Award-Nominees', 'sw' => 'sw/Waliopendekezwa-kupewa-Tuzo'],
            'sponsors' => ['en' => 'Sponsors', 'sw' => 'sw/Wadhamini'],
            'register' => ['en' => 'Register', 'sw' => 'sw/Jisajiri'],
            'download' => ['en' => 'Download', 'sw' => 'sw/Pakua'],
        ];

        $path = $map[strtolower($leaf)][$locale] ?? ($locale === 'sw' ? 'sw' : '');

        return url('/site'.($path !== '' ? '/'.$path : ''));
    }
}
