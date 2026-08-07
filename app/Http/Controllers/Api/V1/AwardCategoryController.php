<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AwardCategory;
use Illuminate\Http\JsonResponse;

class AwardCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = AwardCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get()
            ->map(fn (AwardCategory $category) => [
                'id' => $category->id,
                'name_en' => $category->name_en,
                'name_sw' => $category->name_sw,
                'slug' => $category->slug,
                'description_en' => $category->description_en,
                'description_sw' => $category->description_sw,
                'sort_order' => (int) $category->sort_order,
            ])
            ->values();

        return response()->json([
            'data' => $categories,
            'meta' => ['total' => $categories->count()],
        ]);
    }
}
