<?php

namespace App\Services;

use Illuminate\Support\Facades\Vite;
use Throwable;

/**
 * Hybrid Tailwind restyle for Nicepage HTML:
 * strip heavy CSS bundles, inject Vite assets, improve media loading.
 */
class LegacySiteHtmlTransformer
{
    public function transform(string $html): string
    {
        if ($html === '' || ! str_contains($html, '<html')) {
            return $html;
        }

        $html = $this->stripNicepageStylesheets($html);
        $html = $this->injectAssets($html);
        $html = $this->markTailwindBody($html);
        $html = $this->optimizeImages($html);
        $html = $this->optimizeVideos($html);

        return $html;
    }

    private function stripNicepageStylesheets(string $html): string
    {
        // common-bundle + per-page *-bundle.css (often 100KB–1MB each)
        return (string) preg_replace(
            '/\s*<link\b[^>]*href=["\'][^"\']*css\/(?:common-bundle|[a-f0-9]+-bundle)\.css[^"\']*["\'][^>]*>/i',
            '',
            $html
        );
    }

    private function injectAssets(string $html): string
    {
        $tags = [];

        $tags[] = '<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>';
        $tags[] = '<link href="https://fonts.bunny.net/css?family=dm-serif-display:400|source-sans-3:400,600,700&display=swap" rel="stylesheet">';
        // FA4 glyphs are embedded as SVG <text> with font-family FontAwesome
        $tags[] = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">';

        try {
            $tags[] = Vite::withEntryPoints([
                'resources/css/app.css',
                'resources/js/app.js',
            ])->toHtml();
        } catch (Throwable) {
            // Vite manifest missing during first setup — pages still render.
        }

        $injection = "\n\t<!-- Laravel Tailwind hybrid -->\n\t".implode("\n\t", $tags)."\n";

        if (stripos($html, '</head>') !== false) {
            return (string) preg_replace('/<\/head>/i', $injection.'</head>', $html, 1);
        }

        return $injection.$html;
    }

    private function markTailwindBody(string $html): string
    {
        return (string) preg_replace(
            '/<body\b([^>]*)\bclass=["\']([^"\']*)["\']/i',
            '<body$1class="$2 jk-tw"',
            $html,
            1
        );
    }

    private function optimizeImages(string $html): string
    {
        $count = 0;

        return (string) preg_replace_callback(
            '/<img\b([^>]*)\/?>/i',
            function (array $m) use (&$count): string {
                $attrs = rtrim($m[1]);
                $attrs = rtrim($attrs, '/');
                $attrs = rtrim($attrs);
                $count++;

                if (! preg_match('/\bdecoding=/i', $attrs)) {
                    $attrs .= ' decoding="async"';
                }

                $isLikelyLcp = $count === 1;

                if ($isLikelyLcp) {
                    if (! preg_match('/\bloading=/i', $attrs)) {
                        $attrs .= ' loading="eager"';
                    } elseif (preg_match('/\bloading=["\']lazy["\']/i', $attrs)) {
                        $attrs = (string) preg_replace('/\bloading=["\']lazy["\']/i', 'loading="eager"', $attrs);
                    }
                    if (! preg_match('/\bfetchpriority=/i', $attrs)) {
                        $attrs .= ' fetchpriority="high"';
                    }
                } else {
                    if (! preg_match('/\bloading=/i', $attrs)) {
                        $attrs .= ' loading="lazy"';
                    }
                    if (! preg_match('/\bfetchpriority=/i', $attrs)) {
                        $attrs .= ' fetchpriority="low"';
                    }
                }

                if (preg_match('/\bsrc=["\']([^"\']+)["\']/i', $attrs, $srcMatch)) {
                    $src = $srcMatch[1];
                    if (! preg_match('/\bwidth=/i', $attrs) && preg_match('/_(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)_/i', $src, $dim)) {
                        $attrs .= ' width="'.(int) $dim[1].'" height="'.(int) $dim[2].'"';
                    }
                }

                return '<img'.$attrs.'>';
            },
            $html
        );
    }

    private function optimizeVideos(string $html): string
    {
        $html = (string) preg_replace_callback(
            '/<video\b([^>]*)>/i',
            function (array $m): string {
                $attrs = $m[1];

                if (! preg_match('/\bpreload=/i', $attrs)) {
                    $attrs .= ' preload="none"';
                } else {
                    $attrs = (string) preg_replace('/\bpreload=["\'][^"\']*["\']/i', 'preload="none"', $attrs);
                }

                if (! preg_match('/\bplaysinline/i', $attrs)) {
                    $attrs .= ' playsinline';
                }

                $attrs .= ' data-jk-lazy-video="1"';

                return '<video'.$attrs.'>';
            },
            $html
        );

        $html = (string) preg_replace_callback(
            '/<iframe\b([^>]*)>/i',
            function (array $m): string {
                $attrs = $m[1];
                $isMedia = preg_match('/youtube|vimeo|data-defer-load|youtube-player/i', $attrs);

                if ($isMedia && ! preg_match('/\bloading=/i', $attrs)) {
                    $attrs .= ' loading="lazy"';
                }

                return '<iframe'.$attrs.'>';
            },
            $html
        );

        return $html;
    }
}
