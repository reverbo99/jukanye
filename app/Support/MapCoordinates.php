<?php

namespace App\Support;

class MapCoordinates
{
    /** Arusha city centre — default for festival maps. */
    public const DEFAULT_LAT = -3.3869;

    public const DEFAULT_LNG = 36.6830;

    public const DEFAULT_ZOOM = 14;

    public static function hasCoordinates(?float $lat, ?float $lng): bool
    {
        return $lat !== null && $lng !== null;
    }

    public static function googleMapsUrl(?float $lat, ?float $lng): ?string
    {
        if (! self::hasCoordinates($lat, $lng)) {
            return null;
        }

        return 'https://www.google.com/maps?q='.rawurlencode((string) $lat).','.rawurlencode((string) $lng);
    }

    public static function googleMapsEmbedUrl(?float $lat, ?float $lng, int $zoom = 16): ?string
    {
        if (! self::hasCoordinates($lat, $lng)) {
            return null;
        }

        return 'https://www.google.com/maps?q='.rawurlencode((string) $lat).','.rawurlencode((string) $lng)
            .'&z='.$zoom.'&output=embed';
    }

    public static function openStreetMapUrl(?float $lat, ?float $lng, int $zoom = 16): ?string
    {
        if (! self::hasCoordinates($lat, $lng)) {
            return null;
        }

        return 'https://www.openstreetmap.org/?mlat='.rawurlencode((string) $lat)
            .'&mlon='.rawurlencode((string) $lng)
            .'#map='.$zoom.'/'.rawurlencode((string) $lat).'/'.rawurlencode((string) $lng);
    }

    public static function openStreetMapPreviewUrl(?float $lat, ?float $lng, int $width = 600, int $height = 280): ?string
    {
        if (! self::hasCoordinates($lat, $lng)) {
            return null;
        }

        return 'https://staticmap.openstreetmap.de/staticmap.php?center='
            .rawurlencode((string) $lat).','.rawurlencode((string) $lng)
            .'&zoom=15&size='.$width.'x'.$height
            .'&markers='.rawurlencode((string) $lat).','.rawurlencode((string) $lng).',red-pushpin';
    }

    public static function scriptUrl(): string
    {
        return asset('site/js/jk-osm-map.js').'?v=2';
    }

    public static function scriptTag(): string
    {
        return '<script src="'.e(self::scriptUrl()).'" defer></script>';
    }

    /**
     * Interactive OpenStreetMap canvas. Markers: lat, lng, title?, popup?
     *
     * @param  list<array{lat: float|int|string, lng: float|int|string, title?: string, popup?: string}>  $markers
     */
    public static function canvasHtml(string $id, array $markers, int $height = 280): string
    {
        $payload = [];
        foreach ($markers as $marker) {
            $lat = isset($marker['lat']) ? (float) $marker['lat'] : null;
            $lng = isset($marker['lng']) ? (float) $marker['lng'] : null;
            if (! self::hasCoordinates($lat, $lng)) {
                continue;
            }
            $payload[] = [
                'lat' => $lat,
                'lng' => $lng,
                'title' => (string) ($marker['title'] ?? ''),
                'popup' => (string) ($marker['popup'] ?? ''),
            ];
        }

        $json = htmlspecialchars(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]',
            ENT_QUOTES,
            'UTF-8'
        );
        $height = max(180, $height);

        return '<div class="jk-osm-map" id="'.e($id).'" data-markers="'.$json.'" style="height:'.$height.'px"></div>';
    }
}
