<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\DeepLTranslateService;
use App\Support\Bilingual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::current(),
            'locales' => Bilingual::LOCALES,
        ]);
    }

    public function update(Request $request, DeepLTranslateService $translator): RedirectResponse
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
            'total_raised' => ['nullable', 'integer', 'min:0'],
            'raised_currency' => ['nullable', 'string', 'max:10'],
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
            'write_locale' => ['required', Rule::in(array_keys(Bilingual::LOCALES))],
            'translate_locale' => [
                'required',
                Rule::in(array_keys(Bilingual::LOCALES)),
                'different:write_locale',
            ],
            'deepl_api_key' => ['nullable', 'string', 'max:500'],
            'deepl_api_plan' => ['required', Rule::in(['pro', 'free'])],
            'clear_deepl_api_key' => ['nullable', 'boolean'],
        ]);

        $settings = SiteSetting::current();

        $deeplKey = $settings->deepl_api_key;
        if ($request->boolean('clear_deepl_api_key')) {
            $deeplKey = null;
        } elseif (filled($data['deepl_api_key'] ?? null)) {
            $deeplKey = $data['deepl_api_key'];
        }

        // Persist DeepL prefs first so auth key / plan / direction are available.
        $settings->fill([
            'write_locale' => $data['write_locale'],
            'translate_locale' => $data['translate_locale'],
            'deepl_api_key' => $deeplKey,
            'deepl_api_plan' => $data['deepl_api_plan'],
        ])->save();

        $translated = $translator->fillMissingPairs($data, [
            ['tagline_sw', 'tagline_en'],
            ['donate_body_sw', 'donate_body_en'],
            ['about_intro_sw', 'about_intro_en'],
            ['download_text_sw', 'download_text_en'],
        ], $data['write_locale'], $data['translate_locale']);

        $settings->fill([
            'tagline_en' => $translated['tagline_en'] ?? null,
            'tagline_sw' => $translated['tagline_sw'] ?? null,
            'date_label' => $data['date_label'] ?? null,
            'location_label' => $data['location_label'] ?? null,
            'festival_starts_at' => $data['festival_starts_at'] ?? null,
            'countdown_at' => $data['countdown_at'] ?? null,
            'donate_embed_url' => $data['donate_embed_url'] ?? null,
            'donate_body_en' => $translated['donate_body_en'] ?? null,
            'donate_body_sw' => $translated['donate_body_sw'] ?? null,
            'total_raised' => $data['total_raised'] ?? 0,
            'raised_currency' => $data['raised_currency'] ?? 'TZS',
            'about_intro_en' => $translated['about_intro_en'] ?? null,
            'about_intro_sw' => $translated['about_intro_sw'] ?? null,
            'download_text_en' => $translated['download_text_en'] ?? null,
            'download_text_sw' => $translated['download_text_sw'] ?? null,
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
