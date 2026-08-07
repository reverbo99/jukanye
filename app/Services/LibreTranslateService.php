<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LibreTranslateService
{
    public function translate(string $text, string $source, string $target): string
    {
        $text = trim($text);
        if ($text === '' || $source === $target) {
            return $text;
        }

        try {
            $payload = [
                'q' => $text,
                'source' => $source === 'auto' ? 'auto' : $source,
                'target' => $target,
                'format' => 'text',
            ];

            $apiKey = (string) config('libretranslate.api_key', '');
            if ($apiKey !== '') {
                $payload['api_key'] = $apiKey;
            }

            $response = Http::timeout((int) config('libretranslate.timeout', 20))
                ->acceptJson()
                ->asJson()
                ->post(config('libretranslate.url').'/translate', $payload);

            if ($response->successful()) {
                $translated = trim((string) $response->json('translatedText', ''));
                if ($translated !== '') {
                    return $translated;
                }
            }

            Log::warning('LibreTranslate failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'source' => $source,
                'target' => $target,
            ]);
        } catch (Throwable $e) {
            Log::warning('LibreTranslate exception: '.$e->getMessage(), [
                'source' => $source,
                'target' => $target,
            ]);
        }

        // Keep save working if the translator is down.
        return $text;
    }

    /**
     * Fill empty bilingual fields. Prefer SW → EN when primary is sw.
     *
     * @param  array<string, mixed>  $data
     * @param  list<array{0:string,1:string}>  $pairs  each [swField, enField]
     * @return array<string, mixed>
     */
    public function fillMissingPairs(array $data, array $pairs): array
    {
        foreach ($pairs as [$swField, $enField]) {
            $sw = trim((string) ($data[$swField] ?? ''));
            $en = trim((string) ($data[$enField] ?? ''));

            if ($sw !== '' && $en === '') {
                $data[$enField] = $this->translate($sw, 'sw', 'en');
            } elseif ($en !== '' && $sw === '') {
                $data[$swField] = $this->translate($en, 'en', 'sw');
            }
        }

        return $data;
    }
}
