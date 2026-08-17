<?php

namespace App\Http\Controllers;

use App\Models\MapPlace;
use App\Models\Order;
use App\Models\Person;
use App\Models\Post;
use App\Models\TicketTier;
use App\Models\Tour;
use App\Services\FlutterwaveService;
use App\Support\ApiMedia;
use App\Support\ContentRowSection;
use App\Support\NewsSection;
use App\Support\SiteTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class CmsPublicController extends Controller
{
    /** @var array<string, array{en: string, sw: string, type?: string}> */
    private const PEOPLE_PAGES = [
        'speakers' => ['en' => 'Speakers', 'sw' => 'Speakers', 'type' => 'speaker'],
        'artists' => ['en' => 'Artists', 'sw' => 'Artists', 'type' => 'artist'],
        'heroes' => ['en' => 'Heroes', 'sw' => 'Heroes', 'type' => 'hero'],
        'exhibitions' => ['en' => 'Exhibitions', 'sw' => 'Exhibitions', 'type' => 'exhibition'],
        'friends' => ['en' => 'Friends', 'sw' => 'Friends', 'type' => 'friend'],
    ];

    /**
     * @return array{html: string, leaf: string}|null
     */
    public function panelForPath(string $path): ?array
    {
        $normalized = strtolower(trim($path, '/'));
        $locale = str_starts_with($normalized, 'sw/') || $normalized === 'sw' ? 'sw' : 'en';
        $leaf = preg_replace('#^sw/#', '', $normalized) ?? $normalized;
        if ($leaf === 'sw') {
            $leaf = '';
        }

        if (preg_match('#^news/([^/]+)$#', $leaf, $m)) {
            $html = $this->newsShowHtml($locale, $m[1]);

            return $html === null ? null : ['html' => $html, 'leaf' => 'news'];
        }

        $html = match ($leaf) {
            'news', 'habari' => $this->newsIndexHtml($locale),
            'speakers', 'artists', 'heroes', 'exhibitions', 'friends' => $this->peopleHtml($locale, $leaf),
            'tourism', 'utalii' => $this->toursHtml($locale),
            'tickets', 'tiketi' => $this->ticketsHtml($locale),
            'festival-map', 'ramani' => $this->mapPlacesHtml($locale),
            default => null,
        };

        if ($html === null) {
            return null;
        }

        $navLeaf = match ($leaf) {
            'habari' => 'news',
            'utalii' => 'tourism',
            'tiketi' => 'tickets',
            'ramani' => 'festival-map',
            default => $leaf,
        };

        return ['html' => $html, 'leaf' => $navLeaf];
    }

    public function buyTicket(Request $request, FlutterwaveService $flutterwave): RedirectResponse
    {
        $data = $request->validate([
            'tier_id' => ['required', 'integer', 'exists:ticket_tiers,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $tier = TicketTier::published()->findOrFail($data['tier_id']);
        $order = Order::create([
            'user_id' => auth()->id(),
            'type' => 'ticket',
            'ticket_tier_id' => $tier->id,
            'amount' => (int) $tier->price,
            'currency' => $tier->currency ?: 'TZS',
            'status' => 'pending',
            'customer_name' => $data['name'] ?? auth()->user()?->name,
            'customer_email' => $data['email'] ?? auth()->user()?->email,
            'customer_phone' => $data['phone'] ?? null,
            'reference' => 'JKY-'.strtoupper(Str::random(10)).'-'.time(),
            'provider' => 'flutterwave',
        ]);

        try {
            $initiated = $flutterwave->initiate($order);
            $order->payment_link = $initiated['link'];
            $order->save();
            if ($order->payment_link) {
                return redirect()->away($order->payment_link);
            }
        } catch (RuntimeException $e) {
            return redirect()->to(url('/site/Tickets'))->with('error', $e->getMessage());
        }

        return redirect()->to(url('/site/Tickets'))->with('error', 'Unable to start payment.');
    }

    private function newsIndexHtml(string $locale): string
    {
        $posts = Schema::hasTable('posts')
            ? Post::published()->orderByDesc('published_at')->orderByDesc('id')->get()
            : collect();

        $heading = $locale === 'sw' ? 'Habari' : 'News';
        $empty = $locale === 'sw' ? 'Hakuna habari bado.' : 'No news published yet.';
        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>';

        if ($posts->isEmpty()) {
            return $html.'<p>'.e($empty).'</p></div>';
        }

        return $html.NewsSection::listHtml($posts, $locale).'</div>';
    }

    private function newsShowHtml(string $locale, string $slug): ?string
    {
        if (! Schema::hasTable('posts')) {
            return null;
        }

        $post = Post::published()->where('slug', $slug)->first();
        if (! $post) {
            return null;
        }

        $title = $locale === 'sw' ? ($post->title_sw ?: $post->title_en) : ($post->title_en ?: $post->title_sw);
        $excerpt = $locale === 'sw' ? ($post->excerpt_sw ?: $post->excerpt_en) : ($post->excerpt_en ?: $post->excerpt_sw);
        $body = $locale === 'sw' ? ($post->body_sw ?: $post->body_en) : ($post->body_en ?: $post->body_sw);
        $cover = ApiMedia::url($post->cover_image);
        $dateLabel = optional($post->published_at)?->format('F j, Y');
        $backUrl = $this->pageUrl($locale, 'News');

        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($title).'</h2>';
        if ($dateLabel) {
            $html .= '<p class="jk-cms-meta">'.e($dateLabel).'</p>';
        }
        if ($cover) {
            $html .= '<p><img src="'.e($cover).'" alt="'.e($title).'" style="max-width:100%;border-radius:.55rem"></p>';
        }
        if ($excerpt) {
            $html .= '<p><strong>'.e($excerpt).'</strong></p>';
        }
        if ($body) {
            $html .= '<p>'.nl2br(e($body)).'</p>';
        }
        $html .= '<p><a class="jk-more" href="'.e($backUrl).'">← '
            .e($locale === 'sw' ? 'Habari zote' : 'All news').'</a></p></div>';

        return $html;
    }

    private function peopleHtml(string $locale, string $leaf): string
    {
        $cfg = self::PEOPLE_PAGES[$leaf];
        $cards = [];
        if (Schema::hasTable('people')) {
            $people = Person::published()->where('type', $cfg['type'])->orderBy('sort_order')->get();
            foreach ($people as $person) {
                $subtitle = $locale === 'sw'
                    ? ($person->subtitle_sw ?: $person->subtitle_en)
                    : ($person->subtitle_en ?: $person->subtitle_sw);
                $bio = $locale === 'sw'
                    ? ($person->bio_sw ?: $person->bio_en)
                    : ($person->bio_en ?: $person->bio_sw);
                $cards[] = [
                    'title' => $person->name,
                    'meta' => $subtitle,
                    'body' => $bio,
                    'image' => ApiMedia::url($person->photo),
                ];
            }
        }

        $heading = Person::types()[$cfg['type']] ?? $cfg['en'];
        if ($locale === 'sw') {
            $heading = match ($cfg['type']) {
                'speaker' => 'Wazungumzaji',
                'artist' => 'Wasanii',
                'hero' => 'Mashujaa',
                'friend' => 'Marafiki',
                'exhibition' => 'Maonyesho',
                default => $heading,
            };
        }

        return $this->listingHtml(
            heading: $heading,
            empty: $locale === 'sw' ? 'Hakuna yaliyochapishwa bado.' : 'Nothing published yet.',
            cards: $cards,
        );
    }

    private function toursHtml(string $locale): string
    {
        $cards = [];
        if (Schema::hasTable('tours')) {
            foreach (Tour::published()->orderBy('sort_order')->get() as $tour) {
                $name = $locale === 'sw' ? ($tour->name_sw ?: $tour->name_en) : ($tour->name_en ?: $tour->name_sw);
                $duration = $locale === 'sw' ? ($tour->duration_sw ?: $tour->duration_en) : ($tour->duration_en ?: $tour->duration_sw);
                $desc = $locale === 'sw' ? ($tour->description_sw ?: $tour->description_en) : ($tour->description_en ?: $tour->description_sw);
                $price = $tour->from_price !== null
                    ? (($locale === 'sw' ? 'Kuanzia ' : 'From ').number_format((int) $tour->from_price).' '.$tour->currency)
                    : null;
                $cards[] = [
                    'title' => $name,
                    'meta' => trim(($duration ?: '').($price ? (($duration ? ' · ' : '').$price) : '')),
                    'body' => $desc,
                    'image' => ApiMedia::url($tour->image),
                ];
            }
        }

        return $this->listingHtml(
            heading: $locale === 'sw' ? 'Utalii' : 'Tourism',
            empty: $locale === 'sw' ? 'Hakuna ziara bado.' : 'No tours published yet.',
            cards: $cards,
        );
    }

    private function ticketsHtml(string $locale): string
    {
        $rows = [];
        if (Schema::hasTable('ticket_tiers')) {
            foreach (TicketTier::published()->orderBy('sort_order')->get() as $tier) {
                $name = $locale === 'sw' ? ($tier->name_sw ?: $tier->name_en) : ($tier->name_en ?: $tier->name_sw);
                $rows[] = [
                    'title' => $name,
                    'meta' => number_format((int) $tier->price).' '.$tier->currency,
                    'url' => url('/site/checkout/ticket?tier_id='.$tier->id),
                    'cta' => $locale === 'sw' ? 'Nunua' : 'Buy Now',
                ];
            }
        }

        $heading = $locale === 'sw' ? 'Tiketi' : 'Tickets';
        $empty = $locale === 'sw' ? 'Hakuna tiketi bado.' : 'No ticket tiers published yet.';
        $lead = $locale === 'sw'
            ? 'Chagua aina ya tiketi ya Tamasha la Jukanye.'
            : 'Choose a ticket tier for the Jukanye Festival.';

        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>';
        $html .= '<p class="jk-cms-lead">'.e($lead).'</p>';

        if ($rows === []) {
            return $html.'<p>'.e($empty).'</p></div>';
        }

        foreach ($rows as $row) {
            $html .= '<div class="jk-ticket-row">'
                .'<div><p class="jk-ticket-row__name">'.e($row['title']).'</p>'
                .'<div class="jk-ticket-row__price">'.e($row['meta']).'</div></div>'
                .'<a href="'.e($row['url']).'">'.e($row['cta']).'</a></div>';
        }

        return $html.'</div>';
    }

    private function mapPlacesHtml(string $locale): string
    {
        $cards = [];
        if (Schema::hasTable('map_places')) {
            foreach (MapPlace::published()->orderBy('sort_order')->get() as $place) {
                $name = $locale === 'sw' ? ($place->name_sw ?: $place->name_en) : ($place->name_en ?: $place->name_sw);
                $desc = $locale === 'sw'
                    ? ($place->description_sw ?: $place->description_en)
                    : ($place->description_en ?: $place->description_sw);
                $meta = ($place->lat !== null && $place->lng !== null)
                    ? $place->lat.', '.$place->lng
                    : null;
                $mapsUrl = ($place->lat !== null && $place->lng !== null)
                    ? 'https://www.google.com/maps?q='.$place->lat.','.$place->lng
                    : null;
                $cards[] = [
                    'title' => $name,
                    'meta' => $meta,
                    'body' => $desc,
                    'url' => $mapsUrl,
                    'cta' => $mapsUrl ? ($locale === 'sw' ? 'Fungua ramani' : 'Open in Maps') : null,
                ];
            }
        }

        return $this->listingHtml(
            heading: $locale === 'sw' ? 'Ramani ya Tamasha' : 'Festival Map',
            empty: $locale === 'sw' ? 'Hakuna maeneo bado.' : 'No map places published yet.',
            cards: $cards,
        );
    }

    /**
     * @param  list<array{title: string, body?: ?string, meta?: ?string, image?: ?string, url?: ?string, cta?: ?string}>  $cards
     */
    private function listingHtml(string $heading, string $empty, array $cards, ?string $lead = null): string
    {
        $html = $this->panelCss().'<div class="jk-cms"><h2>'.e($heading).'</h2>';
        if ($lead) {
            $html .= '<p class="jk-cms-lead">'.e($lead).'</p>';
        }

        if ($cards === []) {
            $html .= '<p>'.e($empty).'</p></div>';

            return $html;
        }

        $rows = [];
        foreach ($cards as $card) {
            $rows[] = [
                'image' => $card['image'] ?? null,
                'url' => $card['url'] ?? null,
                'meta' => $card['meta'] ?? null,
                'title' => $card['title'] ?? '',
                'body' => $card['body'] ?? null,
                'cta' => $card['cta'] ?? null,
            ];
        }

        return $html.ContentRowSection::listHtml($rows).'</div>';
    }

    private function panelCss(): string
    {
        return SiteTheme::panelCss();
    }

    private function pageUrl(string $locale, string $alias): string
    {
        return $locale === 'sw' ? url('/site/sw/'.$alias) : url('/site/'.$alias);
    }
}
