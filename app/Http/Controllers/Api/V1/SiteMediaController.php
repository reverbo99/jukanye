<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteMediaItem;
use App\Support\ApiMedia;
use App\Support\YoutubeUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteMediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SiteMediaItem::published()->orderBy('sort_order')->orderBy('id');
        if ($request->filled('slot')) {
            $query->where('slot', $request->string('slot'));
        }

        $items = $query->get()->map(fn (SiteMediaItem $item) => [
            'id' => $item->id,
            'slot' => $item->slot,
            'kind' => $item->kind,
            'image_url' => ApiMedia::url($item->image),
            'youtube_url' => $item->youtube_url,
            'youtube_id' => $item->youtubeId(),
            'youtube_embed_url' => YoutubeUrl::embedUrl($item->youtube_url),
            'youtube_thumbnail_url' => $item->youtubeThumbnail(),
            'title_en' => $item->title_en,
            'title_sw' => $item->title_sw,
            'caption_en' => $item->caption_en,
            'caption_sw' => $item->caption_sw,
            'link' => $item->link,
            'sort_order' => $item->sort_order,
        ]);

        return response()->json(['data' => $items, 'meta' => ['total' => $items->count()]]);
    }
}
