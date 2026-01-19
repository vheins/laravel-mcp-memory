<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function upload(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): Media
    {
        $filename = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $fullFilename = $filename . '.' . $extension;

        // Store physical file
        $path = $file->storeAs($directory, $fullFilename, $disk);

        // Create Database Record
        return Media::create([
            'disk' => $disk,
            'directory' => $directory,
            'filename' => $filename,
            'extension' => $extension,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'aggregate_type' => $this->getAggregateType($file->getMimeType()),
            'size' => $file->getSize(),
            'metadata' => [
                'original_filename' => $file->getClientOriginalName(),
            ],
        ]);
    }

    public function delete(Media $media): bool
    {
        // Delete physical file
        if (Storage::disk($media->disk)->exists($media->directory . '/' . $media->filename . '.' . $media->extension)) {
            Storage::disk($media->disk)->delete($media->directory . '/' . $media->filename . '.' . $media->extension);
        }

        // Delete record
        return $media->delete();
    }

    protected function getAggregateType(?string $mime): string
    {
        if (Str::startsWith($mime, 'image/')) {
            return 'image';
        }
        if (Str::startsWith($mime, 'video/')) {
            return 'video';
        }
        if (Str::startsWith($mime, 'audio/')) {
            return 'audio';
        }
        if ($mime === 'application/pdf') {
            return 'pdf';
        }

        return 'other';
    }
}
