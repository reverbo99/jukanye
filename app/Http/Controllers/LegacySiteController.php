<?php

namespace App\Http\Controllers;

use App\Models\AwardCategory;
use App\Models\FormSubmission;
use App\Models\HomeSection;
use App\Models\Nominee;
use App\Models\Post;
use App\Models\Product;
use App\Models\ScheduleItem;
use App\Models\SiteSetting;
use App\Models\Sponsor;
use App\Models\TeamMember;
use App\Support\ApiMedia;
use App\Support\SiteNav;
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
            if ($cmsPanel !== null) {
                $html = $this->injectSiteNav($html, $this->localeFromPath($path), $cmsPanel['leaf']);
                $html = $this->replaceHomepageMain($html, $cmsPanel['html']);
            } else {
                $html = $this->injectCmsBlocks($html, $path);
            }
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
                $html = $this->prependBeforeFooter($html, $homePanel);
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
        return '<style>.jk-cms{padding:2rem 1.25rem;max-width:960px;margin:0 auto 2rem;font-family:Arial,Helvetica,sans-serif;color:#14221f}.jk-cms h2{font-size:1.6rem;margin:0 0 1rem;color:#0ca3a6}.jk-cms h3{font-size:1.15rem;margin:1.5rem 0 .75rem;color:#14221f}.jk-cms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem}.jk-cms-card{background:#fff;border:1px solid #e4e0d6;border-radius:.5rem;padding:1rem;box-shadow:0 1px 4px rgba(0,0,0,.06)}.jk-cms-card img{max-width:100%;height:auto;display:block;margin-bottom:.5rem}.jk-cms p{line-height:1.5}.jk-cms-meta{color:#0ca3a6;font-size:.9rem}.jk-cms a.jk-more{display:inline-block;margin-top:.75rem;font-weight:700;color:#0ca3a6}</style>';
    }

    private function renderHomeCmsPanel(string $locale): ?string
    {
        $parts = [];

        if (Schema::hasTable('site_settings')) {
            $s = SiteSetting::current();
            $tagline = $locale === 'sw' ? ($s->tagline_sw ?: $s->tagline_en) : ($s->tagline_en ?: $s->tagline_sw);
            $meta = trim(($s->date_label ?: '').(($s->date_label && $s->location_label) ? ' · ' : '').($s->location_label ?: ''));
            $countdown = optional($s->countdown_at ?? $s->festival_starts_at)?->format('Y-m-d H:i');
            $raised = (int) ($s->total_raised ?? 0);
            $currency = $s->raised_currency ?: 'TZS';
            $heroBits = '';
            if ($tagline) {
                $heroBits .= '<p><strong>'.e($tagline).'</strong></p>';
            }
            if ($meta !== '') {
                $heroBits .= '<p class="jk-cms-meta">'.e($meta).'</p>';
            }
            if ($countdown) {
                $label = $locale === 'sw' ? 'Kuhesabu: ' : 'Countdown to: ';
                $heroBits .= '<p class="jk-cms-meta">'.e($label.$countdown).'</p>';
            }
            if ($raised > 0) {
                $raisedLabel = $locale === 'sw' ? 'Jumla iliyokusanywa' : 'Total raised';
                $heroBits .= '<p><strong>'.e($raisedLabel).':</strong> '.e(number_format($raised).' '.$currency).'</p>';
            }
            if ($heroBits !== '') {
                $heading = $locale === 'sw' ? 'Karibu Jukanye' : 'Welcome to Jukanye';
                $parts[] = '<h2>'.e($heading).'</h2>'.$heroBits;
            }
        }

        if (Schema::hasTable('home_sections')) {
            $sections = HomeSection::published()->orderBy('sort_order')->get();
            if ($sections->isNotEmpty()) {
                $heading = $locale === 'sw' ? 'Kuhusu Tamasha' : 'Festival highlights';
                $blocks = '';
                foreach ($sections as $section) {
                    $title = $locale === 'sw'
                        ? ($section->title_sw ?: $section->title_en)
                        : ($section->title_en ?: $section->title_sw);
                    $body = $locale === 'sw'
                        ? ($section->body_sw ?: $section->body_en)
                        : ($section->body_en ?: $section->body_sw);
                    $blocks .= '<div class="jk-cms-card">';
                    if ($title) {
                        $blocks .= '<strong>'.e($title).'</strong>';
                    }
                    if ($body) {
                        $blocks .= '<p>'.nl2br(e($body)).'</p>';
                    }
                    if ($section->link) {
                        $linkLabel = $locale === 'sw' ? 'Soma zaidi' : 'Learn more';
                        $blocks .= '<a class="jk-more" href="'.e($section->link).'">'.e($linkLabel).'</a>';
                    }
                    $blocks .= '</div>';
                }
                $parts[] = '<h2>'.e($heading).'</h2><div class="jk-cms-grid">'.$blocks.'</div>';
            }
        }

        if (Schema::hasTable('posts')) {
            $posts = Post::published()->orderByDesc('published_at')->orderByDesc('id')->limit(4)->get();
            if ($posts->isNotEmpty()) {
                $heading = $locale === 'sw' ? 'Habari mpya' : 'Latest news';
                $cards = '';
                foreach ($posts as $post) {
                    $title = $locale === 'sw'
                        ? ($post->title_sw ?: $post->title_en)
                        : ($post->title_en ?: $post->title_sw);
                    $excerpt = $locale === 'sw'
                        ? ($post->excerpt_sw ?: $post->excerpt_en)
                        : ($post->excerpt_en ?: $post->excerpt_sw);
                    $img = ApiMedia::url($post->cover_image);
                    $url = $locale === 'sw'
                        ? url('/site/sw/News/'.$post->slug)
                        : url('/site/News/'.$post->slug);
                    $cards .= '<div class="jk-cms-card">';
                    if ($img) {
                        $cards .= '<img src="'.e($img).'" alt="'.e($title).'">';
                    }
                    $cards .= '<a href="'.e($url).'"><strong>'.e($title).'</strong></a>';
                    if ($excerpt) {
                        $cards .= '<p>'.e(\Illuminate\Support\Str::limit($excerpt, 120)).'</p>';
                    }
                    $cards .= '</div>';
                }
                $allNews = $locale === 'sw' ? url('/site/sw/News') : url('/site/News');
                $viewAll = $locale === 'sw' ? 'Habari zote' : 'View all news';
                $parts[] = '<h2>'.e($heading).'</h2><div class="jk-cms-grid">'.$cards.'</div>'
                    .'<p><a class="jk-more" href="'.e($allNews).'">'.e($viewAll).'</a></p>';
            }
        }

        if ($parts === []) {
            return null;
        }

        return $this->panelCss().'<div class="jk-cms">'.implode('', $parts).'</div>';
    }

    private function renderAwardsPanel(string $locale): ?string
    {
        if (! Schema::hasTable('nominees')) {
            return null;
        }

        $nominees = Nominee::published()->with('category')->orderBy('sort_order')->get();
        $heading = $locale === 'sw' ? 'Waliopendekezwa' : 'Award nominees';
        $cards = '';

        foreach ($nominees as $nominee) {
            $bio = $locale === 'sw'
                ? ($nominee->bio_sw ?: $nominee->bio_en)
                : ($nominee->bio_en ?: $nominee->bio_sw);
            $cat = $nominee->category
                ? ($locale === 'sw'
                    ? ($nominee->category->name_sw ?: $nominee->category->name_en)
                    : ($nominee->category->name_en ?: $nominee->category->name_sw))
                : null;
            $photo = ApiMedia::url($nominee->photo);
            $cards .= '<div class="jk-cms-card">';
            if ($photo) {
                $cards .= '<img src="'.e($photo).'" alt="'.e($nominee->name).'">';
            }
            $cards .= '<strong>'.e($nominee->name).'</strong>';
            if ($nominee->country) {
                $cards .= '<div class="jk-cms-meta">'.e($nominee->country).'</div>';
            }
            if ($cat) {
                $cards .= '<div class="jk-cms-meta">'.e($cat).'</div>';
            }
            if ($bio) {
                $cards .= '<p>'.nl2br(e($bio)).'</p>';
            }
            $cards .= '</div>';
        }

        if ($cards === '') {
            $cards = '<p>'.e($locale === 'sw' ? 'Hakuna waliopendekezwa bado.' : 'No nominees published yet.').'</p>';
        }

        $categoriesHtml = '';
        if (Schema::hasTable('award_categories')) {
            $categories = AwardCategory::orderBy('sort_order')->get();
            if ($categories->isNotEmpty()) {
                $categoriesHtml = '<h3>'.e($locale === 'sw' ? 'Kategoria' : 'Categories').'</h3><ul>';
                foreach ($categories as $cat) {
                    $name = $locale === 'sw' ? ($cat->name_sw ?: $cat->name_en) : ($cat->name_en ?: $cat->name_sw);
                    $categoriesHtml .= '<li>'.e($name).'</li>';
                }
                $categoriesHtml .= '</ul>';
            }
        }

        return $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>'.$categoriesHtml
            .'<div class="jk-cms-grid">'.$cards.'</div></div>';
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
        $heading = $locale === 'sw' ? 'Bidhaa' : 'Merchandise';
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
        $heading = $locale === 'sw' ? 'Ratiba ya Tamasha' : 'Programme';
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
