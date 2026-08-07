<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait StoresPublicImages
{
    protected function storePublicImage(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder, 'public');

        Media::create([
            'path' => $path,
            'disk' => 'public',
            'mime' => $file->getClientMimeType(),
            'alt' => $file->getClientOriginalName(),
        ]);

        return $path;
    }

    protected function deletePublicImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
