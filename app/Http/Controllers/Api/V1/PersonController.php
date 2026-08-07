<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Support\ApiMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Person::published()->orderBy('sort_order')->orderBy('name');
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        $items = $query->get()->map(fn (Person $p) => $this->transform($p));

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }

    public function show(int $id): JsonResponse
    {
        $person = Person::published()->findOrFail($id);

        return response()->json(['data' => $this->transform($person), 'meta' => (object) []]);
    }

    private function transform(Person $p): array
    {
        $links = $p->links ?? [];
        $normalized = [];
        foreach ($links as $link) {
            if (is_string($link)) {
                $normalized[] = ['label' => null, 'url' => $link];
            } elseif (is_array($link)) {
                $normalized[] = [
                    'label' => $link['label'] ?? null,
                    'url' => $link['url'] ?? '',
                ];
            }
        }

        return [
            'id' => $p->id,
            'type' => $p->type,
            'name' => $p->name,
            'subtitle_en' => $p->subtitle_en,
            'subtitle_sw' => $p->subtitle_sw,
            'photo' => ApiMedia::url($p->photo),
            'bio_en' => $p->bio_en,
            'bio_sw' => $p->bio_sw,
            'links' => $normalized,
            'sort_order' => $p->sort_order,
        ];
    }
}
