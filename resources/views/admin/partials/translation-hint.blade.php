@php
    use App\Support\Bilingual;
    $writeLocale = Bilingual::writeLocale();
    $translateLocale = Bilingual::translateLocale();
    $writeLabel = Bilingual::LOCALES[$writeLocale] ?? $writeLocale;
    $translateLabel = Bilingual::LOCALES[$translateLocale] ?? $translateLocale;
@endphp
<p class="muted" style="margin:0 0 1rem;">
    Write in <strong>{{ $writeLabel }}</strong>. Leave <strong>{{ $translateLabel }}</strong> empty to auto-translate with DeepL on save
    (Site settings → Translation).
</p>
