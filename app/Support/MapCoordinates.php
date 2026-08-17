<?php

namespace App\Support;

class MapCoordinates
{
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
}
