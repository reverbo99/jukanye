<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ScheduleItem;
use App\Models\SiteSetting;
use App\Models\Sponsor;
use App\Models\TeamMember;
use App\Support\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LegacySiteController extends Controller
{
    public function handle(Request $request, ?string $path = null): Response
    {
        $legacyDir = public_path('site');
        $index = $legacyDir.DIRECTORY_SEPARATOR.'site.php';

        if (! is_file($index)) {
            abort(404, 'Legacy site is not installed. Expected public/site/site.php');
        }

        $_SERVER['SCRIPT_NAME'] = ($request->getBasePath() ?: '').'/index.php';
        $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
        unset($_SERVER['HTTP_X_REQUEST_URI']);

        $basePath = rtrim($request->getBasePath(), '/');
        $siteBase = $basePath.'/site/';

        $path = trim((string) $path, '/');

        $_SERVER['REQUEST_URI'] = $siteBase.$path.($request->getQueryString() ? '?'.$request->getQueryString() : '');

        $GLOBALS['LEGACY_BASE_URL'] = $siteBase;
        $GLOBALS['LEGACY_ALLOW_HTTP'] = true;

        $previousCwd = getcwd();
        chdir($legacyDir);

        ob_start();
        try {
            include $index;
        } finally {
            if ($previousCwd !== false) {
                chdir($previousCwd);
            }
        }

        $html = ob_get_clean();

        if ($html) {
            $html = $this->injectCmsBlocks($html, $path);
        }

        return response($html ?: '', 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function injectCmsBlocks(string $html, string $path): string
    {
        $normalized = strtolower(trim($path, '/'));
        $locale = str_starts_with($normalized, 'sw/') ? 'sw' : 'en';
        $leaf = preg_replace('#^sw/#', '', $normalized) ?? $normalized;

        if (in_array($leaf, ['schedule', ''], true) && $leaf === 'schedule') {
            $html = $this->injectScheduleContent($html, $path);
        }

        $panel = match ($leaf) {
            'sponsors', 'wadhamini' => $this->renderSponsorsPanel($locale),
            'about-us', 'shughuli-zetu' => $this->renderAboutPanel($locale),
            'event-products', 'bidhaa-za-tamasha' => $this->renderProductsPanel($locale),
            'donate', 'changia' => $this->renderDonatePanel($locale),
            'download', 'pakua' => $this->renderDownloadPanel($locale),
            default => null,
        };

        if ($panel) {
            $html = $this->prependBeforeFooter($html, $panel);
        }

        return $html;
    }

    private function prependBeforeFooter(string $html, string $panel): string
    {
        if (preg_match('/id="wb_footer[^"]*"/i', $html, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];

            return substr($html, 0, $pos).$panel.substr($html, $pos);
        }

        return str_replace('</body>', $panel.'</body>', $html);
    }

    private function injectScheduleContent(string $html, string $path): string
    {
        if (! Schema::hasTable('schedule_items')) {
            return $html;
        }

        $locale = str_starts_with(strtolower(trim($path, '/')), 'sw/') ? 'sw' : 'en';
        $items = ScheduleItem::published()->orderBy('starts_at')->orderBy('sort_order')->get();
        $inner = $this->renderScheduleHtml($items, $locale);

        $needle = 'id="wb_main_a19884133bfb00abd9131acdd9d24f77" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div>';
        $replacement = 'id="wb_main_a19884133bfb00abd9131acdd9d24f77" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical">'.$inner.'</div>';

        return str_contains($html, $needle) ? str_replace($needle, $replacement, $html) : $html;
    }

    private function panelCss(): string
    {
        return '<style>.jk-cms{padding:2rem 1.25rem;max-width:960px;margin:0 auto 2rem;font-family:Arial,Helvetica,sans-serif;color:#14221f}.jk-cms h2{font-size:1.6rem;margin:0 0 1rem;color:#0ca3a6}.jk-cms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem}.jk-cms-card{background:#fff;border:1px solid #e4e0d6;border-radius:.5rem;padding:1rem;box-shadow:0 1px 4px rgba(0,0,0,.06)}.jk-cms-card img{max-width:100%;height:auto;display:block;margin-bottom:.5rem}.jk-cms p{line-height:1.5}</style>';
    }

    private function renderSponsorsPanel(string $locale): ?string
    {
        if (! Schema::hasTable('sponsors')) {
            return null;
        }
        $items = Sponsor::published()->orderBy('sort_order')->get();
        $heading = $locale === 'sw' ? 'Wadhamini' : 'Sponsors';
        $cards = '';
        foreach ($items as $item) {
            $logo = ApiMedia::url($item->logo);
            $cards .= '<div class="jk-cms-card">';
            if ($logo) {
                $cards .= '<img src="'.e($logo).'" alt="'.e($item->name).'">';
            }
            $name = e($item->name);
            $cards .= $item->url ? '<a href="'.e($item->url).'" target="_blank" rel="noopener">'.$name.'</a>' : "<strong>{$name}</strong>";
            if ($item->tier) {
                $cards .= '<div class="muted">'.e($item->tier).'</div>';
            }
            $cards .= '</div>';
        }
        if ($cards === '') {
            $cards = '<p>'.e($locale === 'sw' ? 'Hakuna wadhamini bado.' : 'No sponsors published yet.').'</p>';
        }

        return $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2><div class="jk-cms-grid">'.$cards.'</div></div>';
    }

    private function renderAboutPanel(string $locale): ?string
    {
        $intro = '';
        if (Schema::hasTable('site_settings')) {
            $s = SiteSetting::current();
            $intro = $locale === 'sw' ? ($s->about_intro_sw ?: $s->about_intro_en) : ($s->about_intro_en ?: $s->about_intro_sw);
        }
        $teamHtml = '';
        if (Schema::hasTable('team_members')) {
            $members = TeamMember::published()->orderBy('sort_order')->get();
            foreach ($members as $m) {
                $role = $locale === 'sw' ? ($m->role_sw ?: $m->role_en) : ($m->role_en ?: $m->role_sw);
                $bio = $locale === 'sw' ? ($m->bio_sw ?: $m->bio_en) : ($m->bio_en ?: $m->bio_sw);
                $photo = ApiMedia::url($m->photo);
                $teamHtml .= '<div class="jk-cms-card">';
                if ($photo) {
                    $teamHtml .= '<img src="'.e($photo).'" alt="'.e($m->name).'">';
                }
                $teamHtml .= '<strong>'.e($m->name).'</strong>';
                if ($role) {
                    $teamHtml .= '<div>'.e($role).'</div>';
                }
                if ($bio) {
                    $teamHtml .= '<p>'.nl2br(e($bio)).'</p>';
                }
                $teamHtml .= '</div>';
            }
        }
        $heading = $locale === 'sw' ? 'Kuhusu sisi' : 'About us';

        return $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>'
            .($intro ? '<p>'.nl2br(e($intro)).'</p>' : '')
            .($teamHtml ? '<div class="jk-cms-grid">'.$teamHtml.'</div>' : '')
            .'</div>';
    }

    private function renderProductsPanel(string $locale): ?string
    {
        if (! Schema::hasTable('products')) {
            return null;
        }
        $items = Product::published()->orderBy('sort_order')->get();
        $heading = $locale === 'sw' ? 'Bidhaa' : 'Event products';
        $cards = '';
        foreach ($items as $p) {
            $name = $locale === 'sw' ? ($p->name_sw ?: $p->name_en) : ($p->name_en ?: $p->name_sw);
            $tag = $locale === 'sw' ? ($p->tagline_sw ?: $p->tagline_en) : ($p->tagline_en ?: $p->tagline_sw);
            $img = ApiMedia::url($p->image);
            $cards .= '<div class="jk-cms-card">';
            if ($img) {
                $cards .= '<img src="'.e($img).'" alt="'.e($name).'">';
            }
            $cards .= '<strong>'.e($name).'</strong><div>'.e(number_format((int) $p->price).' '.$p->currency).'</div>';
            if ($tag) {
                $cards .= '<p>'.e($tag).'</p>';
            }
            $cards .= '</div>';
        }
        if ($cards === '') {
            $cards = '<p>'.e($locale === 'sw' ? 'Hakuna bidhaa.' : 'No products yet.').'</p>';
        }

        return $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2><div class="jk-cms-grid">'.$cards.'</div></div>';
    }

    private function renderDonatePanel(string $locale): ?string
    {
        if (! Schema::hasTable('site_settings')) {
            return null;
        }
        $s = SiteSetting::current();
        $body = $locale === 'sw' ? ($s->donate_body_sw ?: $s->donate_body_en) : ($s->donate_body_en ?: $s->donate_body_sw);
        $heading = $locale === 'sw' ? 'Changia' : 'Donate';
        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>';
        if ($body) {
            $html .= '<p>'.nl2br(e($body)).'</p>';
        }
        if ($s->donate_embed_url) {
            $html .= '<p><a class="wb_button" href="'.e($s->donate_embed_url).'" target="_blank" rel="noopener">'
                .e($locale === 'sw' ? 'Fungua ukurasa wa kuchangia' : 'Open donation page').'</a></p>';
        }
        $html .= '</div>';

        return $html;
    }

    private function renderDownloadPanel(string $locale): ?string
    {
        if (! Schema::hasTable('site_settings')) {
            return null;
        }
        $s = SiteSetting::current();
        $body = $locale === 'sw' ? ($s->download_text_sw ?: $s->download_text_en) : ($s->download_text_en ?: $s->download_text_sw);
        if (! $body) {
            return null;
        }
        $heading = $locale === 'sw' ? 'Pakua' : 'Download';

        return $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2><p>'.nl2br(e($body)).'</p></div>';
    }

    private function renderScheduleHtml($items, string $locale): string
    {
        $titleKey = $locale === 'sw' ? 'title_sw' : 'title_en';
        $descKey = $locale === 'sw' ? 'description_sw' : 'description_en';
        $locKey = $locale === 'sw' ? 'location_sw' : 'location_en';
        $heading = $locale === 'sw' ? 'Ratiba ya Tamasha' : 'Festival Schedule';
        $empty = $locale === 'sw' ? 'Hakuna matukio yaliyochapishwa bado.' : 'No published schedule items yet.';

        $css = '<style>.jk-schedule{padding:2rem 1.25rem;max-width:920px;margin:0 auto;font-family:Arial,Helvetica,sans-serif;color:#14221f}.jk-schedule h2{font-size:1.75rem;margin:0 0 1.25rem;color:#0ca3a6}.jk-schedule-item{border-left:4px solid #c9a227;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.08);padding:1rem 1.1rem;margin-bottom:1rem}.jk-schedule-item time{display:block;font-size:.9rem;color:#5d6b67;margin-bottom:.35rem}.jk-schedule-item h3{margin:0 0 .4rem;font-size:1.15rem}.jk-schedule-item p{margin:.35rem 0 0;line-height:1.5}.jk-schedule-meta{color:#0ca3a6;font-size:.9rem}</style>';

        if ($items->isEmpty()) {
            return $css.'<div class="jk-schedule"><h2>'.e($heading).'</h2><p>'.e($empty).'</p></div>';
        }

        $blocks = '';
        foreach ($items as $item) {
            $when = optional($item->starts_at)?->format('D, M j · H:i') ?? '';
            if ($item->ends_at) {
                $when .= ' – '.$item->ends_at->format('H:i');
            }
            $location = $item->{$locKey} ?: $item->location_en;
            $blocks .= '<article class="jk-schedule-item"><time>'.e($when).'</time><h3>'.e($item->{$titleKey} ?: $item->title_en).'</h3>';
            if ($location || $item->category) {
                $meta = trim(($item->category ?: '').($location ? (($item->category ? ' · ' : '').$location) : ''));
                $blocks .= '<div class="jk-schedule-meta">'.e($meta).'</div>';
            }
            $desc = $item->{$descKey} ?: $item->description_en;
            if ($desc) {
                $blocks .= '<p>'.nl2br(e($desc)).'</p>';
            }
            $blocks .= '</article>';
        }

        return $css.'<div class="jk-schedule"><h2>'.e($heading).'</h2>'.$blocks.'</div>';
    }
}
