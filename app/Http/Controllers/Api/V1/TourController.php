<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Support\ApiMedia;
use Illuminate\Http\JsonResponse;

class TourController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Tour::published()->orderBy('sort_order')->get()
            ->map(fn (Tour $t) => [
                'id' => $t->id,
                'name_en' => $t->name_en,
                'name_sw' => $t->name_sw,
                'from_price' => $t->from_price,
                'currency' => $t->currency,
                'duration_en' => $t->duration_en,
                'duration_sw' => $t->duration_sw,
                'image' => ApiMedia::url($t->image),
                'description_en' => $t->description_en,
                'description_sw' => $t->description_sw,
                'sort_order' => $t->sort_order,
            ]);

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }
}
