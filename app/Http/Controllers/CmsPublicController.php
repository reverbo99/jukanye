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
        $cards = [];
        if (Schema::hasTable('posts')) {
            foreach (Post::published()->orderByDesc('published_at')->orderByDesc('id')->get() as $post) {
                $title = $locale === 'sw'
                    ? ($post->title_sw ?: $post->title_en)
                    : ($post->title_en ?: $post->title_sw);
                $excerpt = $locale === 'sw'
                    ? ($post->excerpt_sw ?: $post->excerpt_en)
                    : ($post->excerpt_en ?: $post->excerpt_sw);
                $cards[] = [
                    'title' => $title,
                    'body' => $excerpt,
                    'image' => ApiMedia::url($post->cover_image),
                    'meta' => optional($post->published_at)?->format('M j, Y'),
                    'url' => $this->pageUrl($locale, 'News/'.$post->slug),
                ];
            }
        }

        return $this->listingHtml(
            heading: $locale === 'sw' ? 'Habari' : 'News',
            empty: $locale === 'sw' ? 'Hakuna habari bado.' : 'No news published yet.',
            cards: $cards,
        );
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
        $cards = [];
        if (Schema::hasTable('ticket_tiers')) {
            foreach (TicketTier::published()->orderBy('sort_order')->get() as $tier) {
                $name = $locale === 'sw' ? ($tier->name_sw ?: $tier->name_en) : ($tier->name_en ?: $tier->name_sw);
                $desc = $locale === 'sw'
                    ? ($tier->description_sw ?: $tier->description_en)
                    : ($tier->description_en ?: $tier->description_sw);
                $includes = is_array($tier->includes) ? implode(', ', $tier->includes) : null;
                $cards[] = [
                    'title' => $name,
                    'meta' => number_format((int) $tier->price).' '.$tier->currency,
                    'body' => trim(($desc ?: '').($includes ? "\n".$includes : '')),
                    'url' => url('/site/checkout/ticket?tier_id='.$tier->id),
                    'cta' => $locale === 'sw' ? 'Nunua' : 'Buy now',
                ];
            }
        }

        return $this->listingHtml(
            heading: $locale === 'sw' ? 'Tiketi' : 'Tickets',
            empty: $locale === 'sw' ? 'Hakuna tiketi bado.' : 'No ticket tiers published yet.',
            cards: $cards,
            lead: $locale === 'sw'
                ? 'Chagua aina ya tiketi ya Tamasha la Jukanye.'
                : 'Choose a ticket tier for the Jukanye Festival.',
        );
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

        $html .= '<div class="jk-cms-grid">';
        foreach ($cards as $card) {
            $html .= '<div class="jk-cms-card">';
            if (! empty($card['image'])) {
                $html .= '<img src="'.e($card['image']).'" alt="'.e($card['title']).'">';
            }
            $title = e($card['title']);
            if (! empty($card['url'])) {
                $html .= '<h3><a href="'.e($card['url']).'">'.$title.'</a></h3>';
            } else {
                $html .= '<h3>'.$title.'</h3>';
            }
            if (! empty($card['meta'])) {
                $html .= '<div class="jk-cms-meta">'.e($card['meta']).'</div>';
            }
            if (! empty($card['body'])) {
                $html .= '<p>'.nl2br(e($card['body'])).'</p>';
            }
            if (! empty($card['url']) && ! empty($card['cta'])) {
                $html .= '<p><a class="jk-more" href="'.e($card['url']).'">'.e($card['cta']).'</a></p>';
            }
            $html .= '</div>';
        }
        $html .= '</div></div>';

        return $html;
    }

    private function panelCss(): string
    {
        return '<style>.jk-cms{padding:2rem 1.25rem;max-width:1100px;margin:0 auto 2rem;font-family:Arial,Helvetica,sans-serif;color:#14221f}.jk-cms h2{font-size:1.75rem;margin:0 0 1rem;color:#0ca3a6}.jk-cms h3{font-size:1.1rem;margin:.2rem 0 .4rem}.jk-cms-lead{color:#5d6b67;margin:-.35rem 0 1.25rem;line-height:1.5}.jk-cms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem}.jk-cms-card{background:#fff;border:1px solid #e4e0d6;border-radius:.5rem;padding:1rem;box-shadow:0 1px 4px rgba(0,0,0,.06)}.jk-cms-card img{width:100%;height:140px;object-fit:cover;border-radius:.35rem;margin-bottom:.65rem;display:block;background:#dde8e6}.jk-cms p{line-height:1.5;margin:.35rem 0 0;color:#5d6b67}.jk-cms-meta{color:#0ca3a6;font-size:.9rem;margin-top:.25rem}.jk-cms a.jk-more,.jk-cms .jk-more{display:inline-block;margin-top:.65rem;font-weight:700;color:#0ca3a6;text-decoration:none}.jk-cms a{color:#0ca3a6}</style>';
    }

    private function pageUrl(string $locale, string $alias): string
    {
        return $locale === 'sw' ? url('/site/sw/'.$alias) : url('/site/'.$alias);
    }
}
