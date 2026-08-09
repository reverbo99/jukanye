<?php

namespace App\Support;

use App\Models\SiteSetting;

class Bilingual
{
    /** Always available CMS locales (code => label). */
    public const LOCALES = [
        'en' => 'English',
        'sw' => 'Kiswahili',
    ];

    public static function writeLocale(): string
    {
        $locale = (string) (SiteSetting::current()->write_locale ?? 'sw');

        return array_key_exists($locale, self::LOCALES) ? $locale : 'sw';
    }

    public static function translateLocale(): string
    {
        $locale = (string) (SiteSetting::current()->translate_locale ?? 'en');

        return array_key_exists($locale, self::LOCALES) ? $locale : 'en';
    }

    /**
     * Validation rules for an EN/SW field pair. Write language is required;
     * translate language may be left empty for DeepL auto-fill.
     *
     * @param  list<string>  $extra
     * @return array<string, list<string>>
     */
    public static function pairRules(string $base, array $extra = ['string', 'max:255'], bool $requiredOnWrite = true): array
    {
        $write = self::writeLocale();
        $enField = $base.'_en';
        $swField = $base.'_sw';

        return [
            $enField => array_merge(
                [$requiredOnWrite && $write === 'en' ? 'required' : 'nullable'],
                $extra
            ),
            $swField => array_merge(
                [$requiredOnWrite && $write === 'sw' ? 'required' : 'nullable'],
                $extra
            ),
        ];
    }
}
