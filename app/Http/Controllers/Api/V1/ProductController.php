<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\ApiMedia;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Product::published()->orderBy('sort_order')->latest()->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name_en' => $p->name_en,
                'name_sw' => $p->name_sw,
                'price' => $p->price,
                'currency' => $p->currency,
                'tagline_en' => $p->tagline_en,
                'tagline_sw' => $p->tagline_sw,
                'description_en' => $p->description_en,
                'description_sw' => $p->description_sw,
                'image' => ApiMedia::url($p->image),
                'sort_order' => $p->sort_order,
            ]);

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }
}
