<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tagline_en' => ['nullable', 'string', 'max:500'],
            'tagline_sw' => ['nullable', 'string', 'max:500'],
            'date_label' => ['nullable', 'string', 'max:255'],
            'location_label' => ['nullable', 'string', 'max:255'],
            'festival_starts_at' => ['nullable', 'date'],
            'countdown_at' => ['nullable', 'date'],
            'donate_embed_url' => ['nullable', 'string', 'max:500'],
            'donate_body_en' => ['nullable', 'string'],
            'donate_body_sw' => ['nullable', 'string'],
            'about_intro_en' => ['nullable', 'string'],
            'about_intro_sw' => ['nullable', 'string'],
            'download_text_en' => ['nullable', 'string'],
            'download_text_sw' => ['nullable', 'string'],
            'footer_email' => ['nullable', 'email', 'max:255'],
            'footer_phone' => ['nullable', 'string', 'max:100'],
            'footer_address' => ['nullable', 'string', 'max:500'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_twitter' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
        ]);

        $settings = SiteSetting::current();
        $settings->fill([
            'tagline_en' => $data['tagline_en'] ?? null,
            'tagline_sw' => $data['tagline_sw'] ?? null,
            'date_label' => $data['date_label'] ?? null,
            'location_label' => $data['location_label'] ?? null,
            'festival_starts_at' => $data['festival_starts_at'] ?? null,
            'countdown_at' => $data['countdown_at'] ?? null,
            'donate_embed_url' => $data['donate_embed_url'] ?? null,
            'donate_body_en' => $data['donate_body_en'] ?? null,
            'donate_body_sw' => $data['donate_body_sw'] ?? null,
            'about_intro_en' => $data['about_intro_en'] ?? null,
            'about_intro_sw' => $data['about_intro_sw'] ?? null,
            'download_text_en' => $data['download_text_en'] ?? null,
            'download_text_sw' => $data['download_text_sw'] ?? null,
            'footer_contact' => [
                'email' => $data['footer_email'] ?? null,
                'phone' => $data['footer_phone'] ?? null,
                'address' => $data['footer_address'] ?? null,
            ],
            'social' => [
                'facebook' => $data['social_facebook'] ?? null,
                'instagram' => $data['social_instagram'] ?? null,
                'twitter' => $data['social_twitter'] ?? null,
                'youtube' => $data['social_youtube'] ?? null,
            ],
        ])->save();

        return redirect()->route('admin.settings.edit')->with('success', 'Settings saved.');
    }
}