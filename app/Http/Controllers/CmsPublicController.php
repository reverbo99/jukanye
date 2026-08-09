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
use App\Support\SiteNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

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

    public function handle(Request $request, string $path = ''): Response|View|null
    {
        $normalized = strtolower(trim($path, '/'));
        $locale = str_starts_with($normalized, 'sw/') || $normalized === 'sw' ? 'sw' : 'en';
        $leaf = preg_replace('#^sw/#', '', $normalized) ?? $normalized;
        if ($leaf === 'sw') {
            $leaf = '';
        }

        if (preg_match('#^news/([^/]+)$#', $leaf, $m)) {
            return $this->newsShow($locale, $m[1]);
        }

        return match ($leaf) {
            'news', 'habari' => $this->newsIndex($locale),
            'speakers', 'artists', 'heroes', 'exhibitions', 'friends' => $this->people($locale, $leaf),
            'tourism', 'utalii' => $this->tours($locale),
            'tickets', 'tiketi' => $this->tickets($locale),
            'festival-map', 'ramani' => $this->mapPlaces($locale),
            default => null,
        };
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

    private function newsIndex(string $locale): View
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

        return $this->listing(
            locale: $locale,
            currentLeaf: 'news',
            heading: $locale === 'sw' ? 'Habari' : 'News',
            empty: $locale === 'sw' ? 'Hakuna habari bado.' : 'No news published yet.',
            cards: $cards,
            enPath: 'News',
            swPath: 'News',
        );
    }

    private function newsShow(string $locale, string $slug): View|Response
    {
        if (! Schema::hasTable('posts')) {
            abort(404);
        }

        $post = Post::published()->where('slug', $slug)->firstOrFail();
        $title = $locale === 'sw' ? ($post->title_sw ?: $post->title_en) : ($post->title_en ?: $post->title_sw);
        $excerpt = $locale === 'sw' ? ($post->excerpt_sw ?: $post->excerpt_en) : ($post->excerpt_en ?: $post->excerpt_sw);
        $body = $locale === 'sw' ? ($post->body_sw ?: $post->body_en) : ($post->body_en ?: $post->body_sw);

        return view('site.news-show', $this->layoutData($locale, 'news', 'News', 'News') + [
            'postTitle' => $title,
            'excerpt' => $excerpt,
            'body' => $body,
            'cover' => ApiMedia::url($post->cover_image),
            'dateLabel' => optional($post->published_at)?->format('F j, Y'),
            'backUrl' => $this->pageUrl($locale, 'News'),
        ]);
    }

    private function people(string $locale, string $leaf): View
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

        $labels = Person::types();
        $heading = $labels[$cfg['type']] ?? $cfg['en'];
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

        return $this->listing(
            locale: $locale,
            currentLeaf: $leaf,
            heading: $heading,
            empty: $locale === 'sw' ? 'Hakuna yaliyochapishwa bado.' : 'Nothing published yet.',
            cards: $cards,
            enPath: $cfg['en'],
            swPath: $cfg['sw'],
        );
    }

    private function tours(string $locale): View
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

        return $this->listing(
            locale: $locale,
            currentLeaf: 'tourism',
            heading: $locale === 'sw' ? 'Utalii' : 'Tourism',
            empty: $locale === 'sw' ? 'Hakuna ziara bado.' : 'No tours published yet.',
            cards: $cards,
            enPath: 'Tourism',
            swPath: 'Tourism',
        );
    }

    private function tickets(string $locale): View
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

        return $this->listing(
            locale: $locale,
            currentLeaf: 'tickets',
            heading: $locale === 'sw' ? 'Tiketi' : 'Tickets',
            empty: $locale === 'sw' ? 'Hakuna tiketi bado.' : 'No ticket tiers published yet.',
            cards: $cards,
            enPath: 'Tickets',
            swPath: 'Tickets',
            lead: $locale === 'sw'
                ? 'Chagua aina ya tiketi ya Tamasha la Jukanye.'
                : 'Choose a ticket tier for the Jukanye Festival.',
        );
    }

    private function mapPlaces(string $locale): View
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

        return $this->listing(
            locale: $locale,
            currentLeaf: 'festival-map',
            heading: $locale === 'sw' ? 'Ramani ya Tamasha' : 'Festival Map',
            empty: $locale === 'sw' ? 'Hakuna maeneo bado.' : 'No map places published yet.',
            cards: $cards,
            enPath: 'Festival-Map',
            swPath: 'Festival-Map',
        );
    }

    /**
     * @param  list<array{title: string, body?: ?string, meta?: ?string, image?: ?string, url?: ?string}>  $cards
     */
    private function listing(
        string $locale,
        string $currentLeaf,
        string $heading,
        string $empty,
        array $cards,
        string $enPath,
        string $swPath,
        ?string $lead = null,
    ): View {
        return view('site.listing', $this->layoutData($locale, $currentLeaf, $enPath, $swPath) + [
            'heading' => $heading,
            'empty' => $empty,
            'cards' => $cards,
            'lead' => $lead,
        ]);
    }

    /**
     * @return array{locale: string, nav: array, currentLeaf: string, enUrl: string, swUrl: string}
     */
    private function layoutData(string $locale, string $currentLeaf, string $enPath, string $swPath): array
    {
        return [
            'locale' => $locale,
            'nav' => SiteNav::items($locale),
            'currentLeaf' => $currentLeaf,
            'enUrl' => url('/site/'.$enPath),
            'swUrl' => url('/site/sw/'.$swPath),
        ];
    }

    private function pageUrl(string $locale, string $alias): string
    {
        return $locale === 'sw' ? url('/site/sw/'.$alias) : url('/site/'.$alias);
    }
}
