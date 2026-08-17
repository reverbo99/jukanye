<?php

namespace App\Support;

class YoutubeUrl
{
    public static function videoId(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function embedUrl(?string $url): ?string
    {
        $id = self::videoId($url);

        return $id ? 'https://www.youtube.com/embed/'.$id : null;
    }

    public static function watchUrl(?string $url): ?string
    {
        $id = self::videoId($url);

        return $id ? 'https://www.youtube.com/watch?v='.$id : null;
    }

    public static function isValid(?string $url): bool
    {
        return self::videoId($url) !== null;
    }
}
