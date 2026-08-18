<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ScheduleItem;
use App\Support\MapCoordinates;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function index(): JsonResponse
    {
        $items = ScheduleItem::published()
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ScheduleItem $item) => [
                'id' => $item->id,
                'starts_at' => optional($item->starts_at)?->toIso8601String(),
                'ends_at' => optional($item->ends_at)?->toIso8601String(),
                'title_en' => $item->title_en,
                'title_sw' => $item->title_sw,
                'description_en' => $item->description_en,
                'description_sw' => $item->description_sw,
                'location_en' => $item->location_en,
                'location_sw' => $item->location_sw,
                'lat' => $item->lat,
                'lng' => $item->lng,
                'maps_url' => MapCoordinates::openStreetMapUrl($item->lat, $item->lng),
                'category' => $item->category,
                'sort_order' => (int) $item->sort_order,
            ])
            ->values();

        return response()->json([
            'data' => $items,
            'meta' => ['total' => $items->count()],
        ]);
    }
}
