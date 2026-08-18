<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MapPlace;
use App\Support\MapCoordinates;
use Illuminate\Http\JsonResponse;

class MapPlaceController extends Controller
{
    public function index(): JsonResponse
    {
        $items = MapPlace::published()->orderBy('sort_order')->get()
            ->map(fn (MapPlace $p) => [
                'id' => $p->id,
                'name_en' => $p->name_en,
                'name_sw' => $p->name_sw,
                'lat' => $p->lat !== null ? (float) $p->lat : null,
                'lng' => $p->lng !== null ? (float) $p->lng : null,
                'description_en' => $p->description_en,
                'description_sw' => $p->description_sw,
                'sort_order' => $p->sort_order,
                'maps_url' => MapCoordinates::openStreetMapUrl(
                    $p->lat !== null ? (float) $p->lat : null,
                    $p->lng !== null ? (float) $p->lng : null
                ),
            ]);

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }
}
