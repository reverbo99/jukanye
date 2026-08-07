<?php

namespace App\Support;

class ApiMedia
{
    /**
     * Absolute URL for a public disk relative path (e.g. "posts/cover.jpg").
     * Uses the current request host so emulator/LAN clients get reachable image URLs
     * even when APP_URL is localhost.
     */
    public static function url(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relative = 'storage/'.ltrim($path, '/');

        if (app()->runningInConsole()) {
            return asset($relative);
        }

        return rtrim(request()->getSchemeAndHttpHost(), '/').'/'.$relative;
    }
}
