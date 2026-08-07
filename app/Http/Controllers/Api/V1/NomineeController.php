<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Nominee;
use App\Support\ApiMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NomineeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Nominee::published()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('category')) {
            $category = $request->string('category')->toString();
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        $nominees = $query->get()->map(fn (Nominee $nominee) => [
            'id' => $nominee->id,
            'name' => $nominee->name,
            'country' => $nominee->country,
            'photo' => ApiMedia::url($nominee->photo),
            'bio_en' => $nominee->bio_en,
            'bio_sw' => $nominee->bio_sw,
            'links' => $this->normalizeLinks($nominee->links),
            'sort_order' => (int) $nominee->sort_order,
            'category' => $nominee->category ? [
                'id' => $nominee->category->id,
                'slug' => $nominee->category->slug,
                'name_en' => $nominee->category->name_en,
                'name_sw' => $nominee->category->name_sw,
            ] : null,
        ])->values();

        return response()->json([
            'data' => $nominees,
            'meta' => ['total' => $nominees->count()],
        ]);
    }

    /**
     * Normalize CMS links into [{label, url}, ...] for stable mobile parsing.
     *
     * @param  mixed  $links
     * @return list<array{label: ?string, url: string}>
     */
    private function normalizeLinks(mixed $links): array
    {
        if (! is_array($links)) {
            return [];
        }

        $normalized = [];

        foreach ($links as $key => $link) {
            if (is_string($link)) {
                $url = trim($link);
                if ($url !== '') {
                    $normalized[] = [
                        'label' => is_string($key) ? $key : null,
                        'url' => $url,
                    ];
                }
                continue;
            }

            if (! is_array($link)) {
                continue;
            }

            $url = $link['url'] ?? $link['href'] ?? $link['link'] ?? null;
            if (! is_string($url) || trim($url) === '') {
                continue;
            }

            $label = $link['label'] ?? $link['title'] ?? $link['name'] ?? null;

            $normalized[] = [
                'label' => is_string($label) ? $label : null,
                'url' => trim($url),
            ];
        }

        return $normalized;
    }
}
