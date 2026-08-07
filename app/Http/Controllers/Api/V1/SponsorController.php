<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use App\Support\ApiMedia;
use Illuminate\Http\JsonResponse;

class SponsorController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Sponsor::published()->orderBy('sort_order')->orderBy('name')->get()
            ->map(fn (Sponsor $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'logo' => ApiMedia::url($s->logo),
                'url' => $s->url,
                'tier' => $s->tier,
                'sort_order' => $s->sort_order,
            ]);

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }
}
