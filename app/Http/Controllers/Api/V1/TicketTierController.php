<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TicketTier;
use Illuminate\Http\JsonResponse;

class TicketTierController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TicketTier::published()->orderBy('sort_order')->get()
            ->map(fn (TicketTier $t) => [
                'id' => $t->id,
                'slug' => $t->slug,
                'name_en' => $t->name_en,
                'name_sw' => $t->name_sw,
                'price' => $t->price,
                'currency' => $t->currency,
                'description_en' => $t->description_en,
                'description_sw' => $t->description_sw,
                'includes' => $t->includes ?? [],
                'sort_order' => $t->sort_order,
            ]);

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }
}
