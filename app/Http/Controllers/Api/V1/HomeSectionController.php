<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = HomeSection::published()->orderBy('sort_order');
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }
        $items = $query->get()->map(fn (HomeSection $s) => [
            'id' => $s->id,
            'type' => $s->type,
            'title_en' => $s->title_en,
            'title_sw' => $s->title_sw,
            'body_en' => $s->body_en,
            'body_sw' => $s->body_sw,
            'link' => $s->link,
            'sort_order' => $s->sort_order,
        ]);

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }
}
