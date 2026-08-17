<?php

namespace App\Http\Controllers;

use App\Models\AwardCategory;
use App\Models\FormSubmission;
use App\Models\HomeSection;
use App\Models\Nominee;
use App\Models\Post;
use App\Models\Product;
use App\Models\ScheduleItem;
use App\Models\SiteMediaItem;
use App\Models\SiteSetting;
use App\Models\Sponsor;
use App\Models\TeamMember;
use App\Support\ApiMedia;
use App\Support\ContentRowSection;
use App\Support\FeatureSection;
use App\Support\MapCoordinates;
use App\Support\NewsSection;
use App\Support\PhotoGridSection;
use App\Support\SiteNav;
use App\Support\SiteTheme;
use App\Support\YoutubeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LegacySiteController extends Controller
{
    public function __construct(
        private readonly CmsPublicController $cmsPublic,
    ) {}

    public function handle(Request $request, ?string $path = null): mixed
    {
        $path = trim((string) $path, '/');

        $this->captureNicepageForm($request, $path);

        $cmsPanel = $this->cmsPublic->panelForPath($path);

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

        // CMS-only pages (Tickets, News, Speakers, …) reuse homepage chrome.
        $renderPath = $path;
        if ($cmsPanel !== null) {
            $normalized = strtolower($path);
            $renderPath = str_starts_with($normalized, 'sw/') || $normalized === 'sw' ? 'sw' : '';
        }

        $_SERVER['REQUEST_URI'] = $siteBase.$renderPath.($request->getQueryString() ? '?'.$request->getQueryString() : '');

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
            $locale = $this->localeFromPath($path);
            $navLeaf = $cmsPanel !== null
                ? $cmsPanel['leaf']
                : $this->leafForNav(preg_replace('#^sw/#', '', strtolower(trim($path, '/'))) ?? '');

            if ($cmsPanel !== null) {
                $html = $this->injectSiteNav($html, $locale, $cmsPanel['leaf']);
                $html = $this->replaceHomepageMain($html, $cmsPanel['html']);
            } else {
                $html = $this->injectCmsBlocks($html, $path);
            }

            $html = SiteTheme::apply($html, $locale, $navLeaf);
        }

        return response($html ?: '', 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function localeFromPath(string $path): string
    {
        $normalized = strtolower(trim($path, '/'));

        return str_starts_with($normalized, 'sw/') || $normalized === 'sw' ? 'sw' : 'en';
    }

    /**
     * Keep Nicepage homepage header/footer; swap main content for CMS pages.
     */
    private function replaceHomepageMain(string $html, string $panelHtml): string
    {
        $replacement = '<div id="wb_main_a19fb429797b0069f950acd7424ca5e8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical">'
            .$panelHtml
            .'</div></div>';

        $replaced = preg_replace(
            '#<div id="wb_main_a19fb429797b0069f950acd7424ca5e8" class="wb_element wb-layout-element" data-plugin="LayoutElement">.*?(?=<div id="wb_footer_a19fb429797b0069f950acd7424ca5e8")#is',
            $replacement,
            $html,
            1,
            $count
        );

        if ($count > 0) {
            return (string) $replaced;
        }

        return $this->prependBeforeFooter($html, $panelHtml);
    }

    private function injectCmsBlocks(string $html, string $path): string
    {
        $normalized = strtolower(trim($path, '/'));
        $locale = str_starts_with($normalized, 'sw/') || $normalized === 'sw' ? 'sw' : 'en';
        $leaf = preg_replace('#^sw/#', '', $normalized) ?? $normalized;
        if ($leaf === 'sw') {
            $leaf = '';
        }

        $html = $this->injectSiteNav($html, $locale, $this->leafForNav($leaf));

        if ($leaf === 'schedule') {
            $html = $this->injectScheduleContent($html, $path);
        }

        if ($leaf === '') {
            $homePanel = $this->renderHomeCmsPanel($locale);
            if ($homePanel) {
                $html = $this->replaceHomepageMain($html, $homePanel);
            }
        }

        $panel = match ($leaf) {
            'sponsors', 'wadhamini' => $this->renderSponsorsPanel($locale),
            'about-us', 'shughuli-zetu' => $this->renderAboutPanel($locale),
            'event-products', 'bidhaa-za-tamasha' => $this->renderProductsPanel($locale),
            'donate', 'changia' => $this->renderDonatePanel($locale),
            'download', 'pakua' => $this->renderDownloadPanel($locale),
            'award-nominees', 'waliopendekezwa-kupewa-tuzo' => $this->renderAwardsPanel($locale),
            'contacts', 'mawasiliano' => $this->renderContactPanel($locale),
            default => null,
        };

        if ($panel) {
            $html = $this->prependBeforeFooter($html, $panel);
        }

        return $html;
    }

    private function leafForNav(string $leaf): string
    {
        return match ($leaf) {
            '', 'homeb', 'mwanzo' => '',
            'about-us', 'shughuli-zetu' => 'about-us',
            'schedule' => 'schedule',
            'event-products', 'bidhaa-za-tamasha' => 'event-products',
            'donate', 'changia' => 'donate',
            'award-nominees', 'waliopendekezwa-kupewa-tuzo' => 'award-nominees',
            'sponsors', 'wadhamini' => 'sponsors',
            'contacts', 'mawasiliano' => 'contacts',
            'register', 'jisajiri' => 'register',
            'download', 'pakua' => 'download',
            default => $leaf,
        };
    }

    /**
     * Persist Nicepage Register/Contact posts into Laravel admin inbox.
     */
    private function captureNicepageForm(Request $request, string $path): void
    {
        if (! $request->isMethod('post') || ! Schema::hasTable('form_submissions')) {
            return;
        }

        if (! $request->filled('wb_form_id')) {
            return;
        }

        // Nicepage honeypot — spam bots fill "message"
        $honeypot = $request->input('message');
        if (is_string($honeypot) && trim($honeypot) !== '') {
            return;
        }

        $formId = (string) $request->input('wb_form_id');
        $form = match ($formId) {
            '66f86979' => 'register',
            '9197457e' => 'contact',
            default => null,
        };

        if ($form === null) {
            $leaf = strtolower(preg_replace('#^sw/#', '', trim($path, '/')) ?? '');
            $form = match (true) {
                str_contains($leaf, 'register') || str_contains($leaf, 'jisajiri') => 'register',
                str_contains($leaf, 'contact') || str_contains($leaf, 'mawasiliano') => 'contact',
                default => null,
            };
        }

        if ($form === null) {
            return;
        }

        $email = null;
        $ordered = [];

        foreach ($request->request->all() as $key => $value) {
            if (! is_string($key) || in_array($key, ['wb_form_id', 'wb_form_uuid', 'secure_token', 'message'], true)) {
                continue;
            }
            if (is_array($value)) {
                $value = implode(', ', array_map('strval', $value));
            }
            if (! is_scalar($value)) {
                continue;
            }
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            // Nicepage: hidden label + visible input share wb_input_N; PHP keeps the submitted value.
            if (preg_match('/^wb_input_(\d+)$/', $key, $m)) {
                $idx = (int) $m[1];
                $labels = $form === 'contact'
                    ? ['Name', 'Email', 'City', 'Message']
                    : ['Name', 'Second Name', 'Organization', 'Country', 'Phone', 'Email', 'Address', 'Participate as'];
                $label = $labels[$idx] ?? ('Field '.$idx);
                $ordered[$label] = $value;
            } else {
                $ordered[$key] = $value;
            }

            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $email = $value;
            }
        }

        if ($ordered === []) {
            return;
        }

        FormSubmission::create([
            'form' => $form,
            'email' => $email,
            'payload' => $ordered,
        ]);
    }

    /**
     * Replace Nicepage menu links with the app-aligned site nav.
     */
    private function injectSiteNav(string $html, string $locale, string $currentLeaf): string
    {
        $lis = SiteNav::renderListHtml($locale, $currentLeaf);

        $replaced = preg_replace_callback(
            '#(<div[^>]*data-plugin="Menu"[^>]*>.*?<ul[^>]*>).*?(</ul>\s*<div class="clearfix"></div>)#is',
            static fn (array $m) => $m[1].$lis.$m[2],
            $html,
            1,
            $count
        );

        if ($count > 0) {
            return (string) $replaced;
        }

        // Fallback: only add Login if we could not replace the full menu.
        if (! str_contains($html, 'jk-nav-login')) {
            $isSw = $locale === 'sw';
            $signedIn = false;
            try {
                $signedIn = auth()->check();
            } catch (\Throwable) {
                $signedIn = false;
            }
            if ($signedIn) {
                $item = '<li class="jk-nav-login"><a href="'.e(url('/admin')).'">Admin</a></li>';
            } else {
                $label = $isSw ? 'Ingia' : 'Login';
                $item = '<li class="jk-nav-login"><a href="'.e(url('/login')).'">'.e($label).'</a></li>';
            }
            $fallback = preg_replace(
                '#</ul>\s*<div class="clearfix"></div>#',
                $item.'</ul><div class="clearfix"></div>',
                $html,
                1,
                $fallbackCount
            );
            if ($fallbackCount > 0) {
                return (string) $fallback;
            }
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

        return str_contains($html, $needle) ? str_replace($needle, $replacement, $html) : $this->prependBeforeFooter($html, $inner);
    }

    private function panelCss(): string
    {
        return SiteTheme::panelCss();
    }

    private function renderHomeCmsPanel(string $locale): ?string
    {
        $parts = [];
        $donateUrl = $locale === 'sw' ? url('/site/sw/Changia') : url('/site/Donate');
        $ticketsUrl = $locale === 'sw' ? url('/site/sw/Tickets') : url('/site/Tickets');

        $heroWelcome = null;
        if (Schema::hasTable('site_settings')) {
            $s = SiteSetting::current();
            $tagline = $locale === 'sw' ? ($s->tagline_sw ?: $s->tagline_en) : ($s->tagline_en ?: $s->tagline_sw);
            $meta = trim(($s->date_label ?: '').(($s->date_label && $s->location_label) ? ' · ' : '').($s->location_label ?: ''));

            if ($tagline || $meta !== '') {
                $welcome = $locale === 'sw' ? 'Karibu Jukanye' : 'Welcome to Jukanye';
                $heroWelcome = '<div class="jk-media-slider__overlay">'
                    .'<h2>'.e($welcome).'</h2>';
                if ($tagline) {
                    $heroWelcome .= '<p class="jk-cms-lead"><strong>'.e($tagline).'</strong></p>';
                }
                if ($meta !== '') {
                    $heroWelcome .= '<p class="jk-cms-meta">'.e($meta).'</p>';
                }
                $heroWelcome .= '<p class="jk-hero-actions">'
                    .'<a class="jk-btn-gold" href="'.e($ticketsUrl).'">'.e($locale === 'sw' ? 'Nunua Tiketi' : 'Buy Tickets').'</a>'
                    .'<a class="jk-btn-green" href="'.e($donateUrl).'">'.e($locale === 'sw' ? 'Changia Sasa' : 'Donate Now').'</a>'
                    .'</p></div>';
            }
        }

        $heroSlider = $this->renderMediaSliderPanel($locale, 'hero_slider', $heroWelcome);
        if ($heroSlider) {
            $parts[] = $heroSlider;
        }

        if (Schema::hasTable('site_settings')) {
            $s = SiteSetting::current();
            $raised = (int) ($s->total_raised ?? 0);
            $currency = $s->raised_currency ?: 'TZS';

            if ($raised > 0) {
                $title = $locale === 'sw'
                    ? 'Pamoja Tunajenga Tamasha Kubwa Zaidi la Afrika'
                    : "Together We Are Building Africa's Greatest Festival";
                $raisedLabel = $locale === 'sw' ? 'Jumla iliyokusanywa' : 'Total Raised';
                $parts[] = '<div class="jk-contribute-card">'
                    .'<p class="jk-contribute-card__title">'.e($title).'</p>'
                    .'<p class="jk-contribute-card__label">'.e($raisedLabel).'</p>'
                    .'<p class="jk-contribute-card__amount">'.e(number_format($raised).' '.$currency).'</p>'
                    .'<a class="jk-btn-green" href="'.e($donateUrl).'">'.e($locale === 'sw' ? 'Changia Sasa' : 'Contribute Now').'</a>'
                    .'</div>';
            }
        }

        if (Schema::hasTable('home_sections')) {
            $sections = HomeSection::published()->orderBy('sort_order')->get();
            if ($sections->isNotEmpty()) {
                $parts[] = FeatureSection::sectionHtml($sections, $locale);
            }
        }

        if (Schema::hasTable('posts')) {
            $posts = Post::published()->orderByDesc('published_at')->orderByDesc('id')->limit(5)->get();
            if ($posts->isNotEmpty()) {
                $parts[] = NewsSection::sectionHtml($locale, $posts);
            }
        }

        $bannerSlider = $this->renderMediaSliderPanel($locale, 'banner_slider');
        if ($bannerSlider) {
            $parts[] = $bannerSlider;
        }

        $videos = $this->renderFeaturedVideosPanel($locale);
        if ($videos) {
            $parts[] = $videos;
        }

        $gallery = $this->renderMediaGridPanel($locale, 'gallery');
        if ($gallery) {
            $parts[] = $gallery;
        }

        if ($parts === []) {
            return null;
        }

        return $this->panelCss().'<div class="jk-cms jk-cms-home">'.implode('', $parts).'</div>';
    }

    private function renderAwardsPanel(string $locale): ?string
    {
        if (! Schema::hasTable('nominees')) {
            return null;
        }

        $nominees = Nominee::published()->with('category')->orderBy('sort_order')->get();
        $heading = $locale === 'sw' ? 'Waliopendekezwa' : 'Award nominees';
        $rows = [];

        foreach ($nominees as $nominee) {
            $bio = $locale === 'sw'
                ? ($nominee->bio_sw ?: $nominee->bio_en)
                : ($nominee->bio_en ?: $nominee->bio_sw);
            $cat = $nominee->category
                ? ($locale === 'sw'
                    ? ($nominee->category->name_sw ?: $nominee->category->name_en)
                    : ($nominee->category->name_en ?: $nominee->category->name_sw))
                : null;
            $meta = trim(collect([$nominee->country, $cat])->filter()->implode(' · '));
            $rows[] = [
                'image' => ApiMedia::url($nominee->photo),
                'title' => $nominee->name,
                'meta' => $meta !== '' ? $meta : null,
                'body' => $bio,
            ];
        }

        $categoriesHtml = '';
        if (Schema::hasTable('award_categories')) {
            $categories = AwardCategory::orderBy('sort_order')->get();
            if ($categories->isNotEmpty()) {
                $categoriesHtml = '<h3>'.e($locale === 'sw' ? 'Kategoria' : 'Categories').'</h3><ul class="jk-inline-tags">';
                foreach ($categories as $cat) {
                    $name = $locale === 'sw' ? ($cat->name_sw ?: $cat->name_en) : ($cat->name_en ?: $cat->name_sw);
                    $categoriesHtml .= '<li>'.e($name).'</li>';
                }
                $categoriesHtml .= '</ul>';
            }
        }

        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>'.$categoriesHtml;

        if ($rows === []) {
            return $html.'<p>'.e($locale === 'sw' ? 'Hakuna waliopendekezwa bado.' : 'No nominees published yet.').'</p></div>';
        }

        return $html.ContentRowSection::listHtml($rows).'</div>';
    }

    private function renderSponsorsPanel(string $locale): ?string
    {
        if (! Schema::hasTable('sponsors')) {
            return null;
        }
        $items = Sponsor::published()->orderBy('sort_order')->get();
        $heading = $locale === 'sw' ? 'Wadhamini' : 'Sponsors';
        $photos = [];
        foreach ($items as $item) {
            $logo = ApiMedia::url($item->logo);
            if (! $logo) {
                continue;
            }
            $photos[] = [
                'image' => $logo,
                'title' => $item->name,
                'url' => $item->url,
            ];
        }

        $html = $this->panelCss().'<div class="jk-cms">';

        if ($photos === []) {
            return $html.'<h2>'.e($heading).'</h2><p>'.e($locale === 'sw' ? 'Hakuna wadhamini bado.' : 'No sponsors published yet.').'</p></div>';
        }

        return $html.PhotoGridSection::sectionHtml($heading, $photos).'</div>';
    }

    private function renderAboutPanel(string $locale): ?string
    {
        $intro = '';
        if (Schema::hasTable('site_settings')) {
            $s = SiteSetting::current();
            $intro = $locale === 'sw' ? ($s->about_intro_sw ?: $s->about_intro_en) : ($s->about_intro_en ?: $s->about_intro_sw);
        }
        $rows = [];
        if (Schema::hasTable('team_members')) {
            $members = TeamMember::published()->orderBy('sort_order')->get();
            foreach ($members as $m) {
                $role = $locale === 'sw' ? ($m->role_sw ?: $m->role_en) : ($m->role_en ?: $m->role_sw);
                $bio = $locale === 'sw' ? ($m->bio_sw ?: $m->bio_en) : ($m->bio_en ?: $m->bio_sw);
                $rows[] = [
                    'image' => ApiMedia::url($m->photo),
                    'title' => $m->name,
                    'meta' => $role,
                    'body' => $bio,
                ];
            }
        }
        $heading = $locale === 'sw' ? 'Kuhusu sisi' : 'About us';

        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>'
            .($intro ? '<p class="jk-cms-lead">'.nl2br(e($intro)).'</p>' : '');

        if ($rows === []) {
            return $html.'</div>';
        }

        return $html.ContentRowSection::listHtml($rows).'</div>';
    }

    private function renderProductsPanel(string $locale): ?string
    {
        if (! Schema::hasTable('products')) {
            return null;
        }
        $items = Product::published()->orderBy('sort_order')->get();
        $heading = $locale === 'sw' ? 'Bidhaa' : 'Merchandise';
        $rows = [];
        foreach ($items as $p) {
            $name = $locale === 'sw' ? ($p->name_sw ?: $p->name_en) : ($p->name_en ?: $p->name_sw);
            $tag = $locale === 'sw' ? ($p->tagline_sw ?: $p->tagline_en) : ($p->tagline_en ?: $p->tagline_sw);
            $rows[] = [
                'image' => ApiMedia::url($p->image),
                'title' => $name,
                'meta' => number_format((int) $p->price).' '.$p->currency,
                'body' => $tag,
            ];
        }

        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>';

        if ($rows === []) {
            return $html.'<p>'.e($locale === 'sw' ? 'Hakuna bidhaa.' : 'No products yet.').'</p></div>';
        }

        return $html.ContentRowSection::listHtml($rows).'</div>';
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
            $html .= '<p><a class="jk-btn-green" href="'.e($s->donate_embed_url).'" target="_blank" rel="noopener">'
                .e($locale === 'sw' ? 'Fungua ukurasa wa kuchangia' : 'Open donation page').'</a></p>';
        }
        $html .= '</div>';

        return $html;
    }

    private function renderContactPanel(string $locale): ?string
    {
        if (! Schema::hasTable('site_settings')) {
            return null;
        }

        $s = SiteSetting::current();
        $fc = $s->footer_contact ?? [];
        $soc = $s->social ?? [];
        $heading = $locale === 'sw' ? 'Mawasiliano' : 'Contact';
        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>';

        $rows = '';
        foreach (['email' => 'Email', 'phone' => $locale === 'sw' ? 'Simu' : 'Phone', 'address' => $locale === 'sw' ? 'Anwani' : 'Address'] as $key => $label) {
            $val = trim((string) ($fc[$key] ?? ''));
            if ($val === '') {
                continue;
            }
            if ($key === 'email') {
                $rows .= '<p><strong>'.e($label).':</strong> <a href="mailto:'.e($val).'">'.e($val).'</a></p>';
            } elseif ($key === 'phone') {
                $rows .= '<p><strong>'.e($label).':</strong> <a href="tel:'.e(preg_replace('/\s+/', '', $val) ?? $val).'">'.e($val).'</a></p>';
            } else {
                $rows .= '<p><strong>'.e($label).':</strong> '.e($val).'</p>';
            }
        }

        $social = '';
        foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'twitter' => 'Twitter / X', 'youtube' => 'YouTube'] as $key => $label) {
            $val = trim((string) ($soc[$key] ?? ''));
            if ($val === '') {
                continue;
            }
            $social .= '<li><a href="'.e($val).'" target="_blank" rel="noopener">'.e($label).'</a></li>';
        }

        if ($rows === '' && $social === '') {
            $html .= '<p>'.e($locale === 'sw' ? 'Hakuna maelezo ya mawasiliano bado.' : 'No contact details published yet.').'</p>';
        } else {
            $html .= $rows;
            if ($social !== '') {
                $html .= '<h3>'.e($locale === 'sw' ? 'Mitandao ya kijamii' : 'Social').'</h3><ul>'.$social.'</ul>';
            }
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
        $heading = $locale === 'sw' ? 'Ratiba ya Tamasha' : 'Festival Programme';
        $empty = $locale === 'sw' ? 'Hakuna matukio yaliyochapishwa bado.' : 'No published schedule items yet.';

        if ($items->isEmpty()) {
            return $this->panelCss().'<div class="jk-schedule"><h2>'.e($heading).'</h2><p>'.e($empty).'</p></div>';
        }

        $blocks = '';
        foreach ($items as $item) {
            $starts = $item->starts_at;
            $day = optional($starts)?->format('j') ?? '';
            $month = strtoupper(optional($starts)?->format('M') ?? '');
            $when = optional($starts)?->format('H:i') ?? '';
            if ($item->ends_at) {
                $when .= ' – '.$item->ends_at->format('H:i');
            }
            $location = $item->{$locKey} ?: $item->location_en;
            $title = $item->{$titleKey} ?: $item->title_en;
            $desc = $item->{$descKey} ?: $item->description_en;
            $hasMap = $item->hasMapCoordinates();
            $mapsUrl = MapCoordinates::googleMapsUrl($item->lat, $item->lng);
            $embedUrl = MapCoordinates::googleMapsEmbedUrl($item->lat, $item->lng);
            $openMapLabel = $locale === 'sw' ? 'Fungua kwenye ramani' : 'Open in Google Maps';
            $tapHint = $locale === 'sw' ? 'Bofya kwa maelezo na ramani' : 'Tap for details & map';
            $mapHeading = $locale === 'sw' ? 'Mahali pa tukio' : 'Event location';

            $blocks .= '<details class="jk-schedule-item'.($hasMap ? ' jk-schedule-item--has-map' : '').'">';
            $blocks .= '<summary class="jk-schedule-item__summary">';
            $blocks .= '<div class="jk-schedule-item__date"><span class="jk-schedule-item__day">'.e($day).'</span>'
                .'<span class="jk-schedule-item__month">'.e($month).'</span></div>';
            $blocks .= '<div class="jk-schedule-item__head">';
            $blocks .= '<h3>'.e($title).'</h3>';
            if ($location || $item->category || $when) {
                $meta = trim(collect([$item->category, $location, $when])->filter()->implode(' · '));
                $blocks .= '<div class="jk-schedule-meta">'.e($meta).'</div>';
            }
            $blocks .= '<span class="jk-schedule-item__hint">'.e($tapHint).'</span>';
            $blocks .= '</div></summary>';
            $blocks .= '<div class="jk-schedule-item__panel">';
            if ($desc) {
                $blocks .= '<p>'.nl2br(e($desc)).'</p>';
            }
            if ($hasMap && $embedUrl && $mapsUrl) {
                $blocks .= '<div class="jk-schedule-map"><h4>'.e($mapHeading).'</h4>';
                $blocks .= '<iframe title="'.e($title).' — '.e($mapHeading).'" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="'.e($embedUrl).'"></iframe>';
                $blocks .= '<a class="jk-schedule-map__link" href="'.e($mapsUrl).'" target="_blank" rel="noopener">'.e($openMapLabel).'</a>';
                $blocks .= '</div>';
            }
            $blocks .= '</div></details>';
        }

        return $this->panelCss().'<div class="jk-schedule"><h2>'.e($heading).'</h2>'.$blocks.'</div>';
    }

    private function renderMediaSliderPanel(string $locale, string $slot, ?string $overlayHtml = null): ?string
    {
        if (! Schema::hasTable('site_media_items')) {
            return null;
        }

        $items = SiteMediaItem::published()->inSlot($slot)->orderBy('sort_order')->get();
        if ($items->isEmpty()) {
            return null;
        }

        $slides = '';
        foreach ($items as $item) {
            $title = $locale === 'sw'
                ? ($item->title_sw ?: $item->title_en)
                : ($item->title_en ?: $item->title_sw);
            $caption = $locale === 'sw'
                ? ($item->caption_sw ?: $item->caption_en)
                : ($item->caption_en ?: $item->caption_sw);

            if ($item->kind === SiteMediaItem::KIND_YOUTUBE) {
                $thumb = $item->youtubeThumbnail();
                $watch = YoutubeUrl::watchUrl($item->youtube_url);
                if (! $thumb || ! $watch) {
                    continue;
                }
                $slides .= '<div class="jk-media-slide jk-media-slide--video">'
                    .'<a href="'.e($watch).'" target="_blank" rel="noopener">'
                    .'<img src="'.e($thumb).'" alt="'.e($title ?: 'Video').'">'
                    .'<span class="jk-media-play" aria-hidden="true">▶</span></a>';
                if ($title) {
                    $slides .= '<div class="jk-media-slide__cap">'.e($title).'</div>';
                }
                $slides .= '</div>';
            } else {
                $img = ApiMedia::url($item->image);
                if (! $img) {
                    continue;
                }
                $wrapStart = $item->link
                    ? '<a href="'.e($item->link).'" class="jk-media-slide jk-media-slide--link">'
                    : '<div class="jk-media-slide">';
                $wrapEnd = $item->link ? '</a>' : '</div>';
                $slides .= $wrapStart.'<img src="'.e($img).'" alt="'.e($title ?: 'Slide').'">';
                if ($title || $caption) {
                    $slides .= '<div class="jk-media-slide__cap">'.e($title ?: '').($caption ? '<span>'.e($caption).'</span>' : '').'</div>';
                }
                $slides .= $wrapEnd;
            }
        }

        if ($slides === '') {
            return null;
        }

        $heading = match ($slot) {
            'hero_slider' => $overlayHtml === null
                ? ($locale === 'sw' ? 'Picha kuu' : 'Festival highlights')
                : '',
            'banner_slider' => $locale === 'sw' ? 'Wadhamini & matangazo' : 'Partners & banners',
            default => '',
        };

        $intervalMs = match ($slot) {
            'hero_slider' => 8000,
            'banner_slider' => 4500,
            default => 6000,
        };

        $heroClass = $overlayHtml !== null ? ' jk-media-slider--hero' : '';

        $html = '<div class="jk-media-slider'.$heroClass.'" data-jk-slider data-interval="'.$intervalMs.'">';
        if ($heading !== '') {
            $html .= '<h2>'.e($heading).'</h2>';
        }
        $html .= '<div class="jk-media-slider__viewport">'
            .'<div class="jk-media-slider__track">'.$slides.'</div>'
            .($overlayHtml ?? '')
            .'</div>'
            .'<div class="jk-media-slider__dots" data-jk-slider-dots></div></div>'
            .'<script>(function(){var dots=document.currentScript.previousElementSibling;if(!dots)return;var slider=dots.closest("[data-jk-slider]");if(!slider)return;var track=slider.querySelector(".jk-media-slider__track");var dotRoot=slider.querySelector("[data-jk-slider-dots]");if(!track||!track.children.length)return;var ms=+(slider.getAttribute("data-interval")||6000);var i=0;var count=track.children.length;function go(n){i=(n+count)%count;track.style.transform="translateX(-"+(i*100/count)+"%)";if(dotRoot){Array.from(dotRoot.children).forEach(function(dot,idx){dot.classList.toggle("is-active",idx===i);});}}if(count>1){for(var c=0;c<count;c++){var btn=document.createElement("button");btn.type="button";btn.dataset.i=c;btn.addEventListener("click",function(){go(+this.dataset.i);});if(c===0)btn.classList.add("is-active");dotRoot.appendChild(btn);}setInterval(function(){go(i+1);},ms);}})();</script>';

        return $html;
    }

    private function renderFeaturedVideosPanel(string $locale): ?string
    {
        if (! Schema::hasTable('site_media_items')) {
            return null;
        }

        $items = SiteMediaItem::published()
            ->inSlot('featured_videos')
            ->where('kind', SiteMediaItem::KIND_YOUTUBE)
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        $rows = [];
        foreach ($items as $item) {
            $title = $locale === 'sw'
                ? ($item->title_sw ?: $item->title_en)
                : ($item->title_en ?: $item->title_sw);
            $watch = YoutubeUrl::watchUrl($item->youtube_url);
            $thumb = $item->youtubeThumbnail();
            if (! $watch || ! $thumb) {
                continue;
            }
            $rows[] = [
                'image' => $thumb,
                'url' => $watch,
                'meta' => $locale === 'sw' ? 'Video' : 'Video',
                'title' => $title ?: ($locale === 'sw' ? 'Video maalum' : 'Featured video'),
                'cta' => $locale === 'sw' ? 'Tazama' : 'Watch',
                'external' => true,
                'video' => true,
            ];
        }

        if ($rows === []) {
            return null;
        }

        $heading = $locale === 'sw' ? 'Video maalum' : 'Featured videos';

        return ContentRowSection::sectionHtml($heading, $rows);
    }

    private function renderMediaGridPanel(string $locale, string $slot): ?string
    {
        if (! Schema::hasTable('site_media_items')) {
            return null;
        }

        $items = SiteMediaItem::published()
            ->inSlot($slot)
            ->where('kind', SiteMediaItem::KIND_IMAGE)
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        $photos = [];
        foreach ($items as $item) {
            $img = ApiMedia::url($item->image);
            if (! $img) {
                continue;
            }
            $title = $locale === 'sw'
                ? ($item->title_sw ?: $item->title_en)
                : ($item->title_en ?: $item->title_sw);
            $photos[] = [
                'image' => $img,
                'title' => $title,
                'url' => $item->link,
            ];
        }

        if ($photos === []) {
            return null;
        }

        $heading = $locale === 'sw' ? 'Picha' : 'Gallery';

        return PhotoGridSection::sectionHtml($heading, $photos);
    }
}
