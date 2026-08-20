<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function show(): JsonResponse
    {
        $s = SiteSetting::current();

        return response()->json([
            'data' => [
                'tagline_en' => $s->tagline_en,
                'tagline_sw' => $s->tagline_sw,
                'date_label' => $s->date_label,
                'location_label' => $s->location_label,
                'festival_starts_at' => optional($s->festival_starts_at)?->toIso8601String(),
                'countdown_at' => optional($s->countdown_at)?->toIso8601String(),
                'donate_embed_url' => $s->donate_embed_url,
                'donate_body_en' => $s->donate_body_en,
                'donate_body_sw' => $s->donate_body_sw,
                'total_raised' => (int) ($s->total_raised ?? 0),
                'raised_currency' => $s->raised_currency ?: 'TZS',
                'about_intro_en' => $s->about_intro_en,
                'about_intro_sw' => $s->about_intro_sw,
                'download_text_en' => $s->download_text_en,
                'download_text_sw' => $s->download_text_sw,
                'vote_apk_url' => config('services.vote.url'),
                'footer_contact' => $s->footer_contact ?? (object) [],
                'social' => $s->social ?? (object) [],
            ],
            'meta' => (object) [],
        ]);
    }
}
