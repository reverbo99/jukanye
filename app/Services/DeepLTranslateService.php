<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Support\Bilingual;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeepLTranslateService
{
    public function translate(string $text, string $source, string $target): string
    {
        $text = trim($text);
        $source = strtolower($source);
        $target = strtolower($target);

        if ($text === '' || $source === $target) {
            return $text;
        }

        if (! array_key_exists($source, Bilingual::LOCALES) || ! array_key_exists($target, Bilingual::LOCALES)) {
            return $text;
        }

        $authKey = $this->authKey();
        if ($authKey === '') {
            Log::warning('DeepL skipped: no API key configured');

            return $text;
        }

        try {
            $response = Http::timeout((int) config('services.deepl.timeout', 20))
                ->withHeaders([
                    'Authorization' => 'DeepL-Auth-Key '.$authKey,
                ])
                ->acceptJson()
                ->asJson()
                ->post($this->endpoint().'/v2/translate', [
                    'text' => [$text],
                    'source_lang' => strtoupper($source),
                    'target_lang' => strtoupper($target),
                ]);

            if ($response->successful()) {
                $translated = trim((string) data_get($response->json(), 'translations.0.text', ''));
                if ($translated !== '') {
                    return $translated;
                }
            }

            Log::warning('DeepL failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'source' => $source,
                'target' => $target,
            ]);
        } catch (Throwable $e) {
            Log::warning('DeepL exception: '.$e->getMessage(), [
                'source' => $source,
                'target' => $target,
            ]);
        }

        // Keep saves working if the translator is down.
        return $text;
    }

    /**
     * Fill empty bilingual fields using write → translate direction.
     *
     * @param  array<string, mixed>  $data
     * @param  list<array{0:string,1:string}>  $pairs  each [swField, enField]
     * @return array<string, mixed>
     */
    public function fillMissingPairs(array $data, array $pairs, ?string $write = null, ?string $target = null): array
    {
        $write = $write ?: Bilingual::writeLocale();
        $target = $target ?: Bilingual::translateLocale();

        if (! array_key_exists($write, Bilingual::LOCALES)) {
            $write = 'sw';
        }
        if (! array_key_exists($target, Bilingual::LOCALES)) {
            $target = 'en';
        }

        if ($write === $target) {
            return $data;
        }

        foreach ($pairs as [$swField, $enField]) {
            $writeField = $write === 'sw' ? $swField : $enField;
            $targetField = $target === 'sw' ? $swField : $enField;

            $source = trim((string) ($data[$writeField] ?? ''));
            $dest = trim((string) ($data[$targetField] ?? ''));

            if ($source !== '' && $dest === '') {
                $data[$targetField] = $this->translate($source, $write, $target);
            }
        }

        return $data;
    }

    private function authKey(): string
    {
        $settings = SiteSetting::current();
        $fromSettings = trim((string) ($settings->deepl_api_key ?? ''));
        if ($fromSettings !== '') {
            return $fromSettings;
        }

        return trim((string) config('services.deepl.auth_key', ''));
    }

    private function endpoint(): string
    {
        $settings = SiteSetting::current();
        $plan = strtolower((string) ($settings->deepl_api_plan ?? config('services.deepl.plan', 'pro')));

        if ($plan === 'free') {
            return rtrim((string) config('services.deepl.free_url', 'https://api-free.deepl.com'), '/');
        }

        return rtrim((string) config('services.deepl.pro_url', 'https://api.deepl.com'), '/');
    }
}
