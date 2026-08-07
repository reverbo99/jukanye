<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Support\ApiMedia;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TeamMember::published()->orderBy('sort_order')->orderBy('name')->get()
            ->map(fn (TeamMember $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'role_en' => $m->role_en,
                'role_sw' => $m->role_sw,
                'photo' => ApiMedia::url($m->photo),
                'bio_en' => $m->bio_en,
                'bio_sw' => $m->bio_sw,
                'sort_order' => $m->sort_order,
            ]);

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }
}
