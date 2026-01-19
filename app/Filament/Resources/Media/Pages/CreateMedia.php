<?php

namespace App\Filament\Resources\Media\Pages;



use App\Filament\Resources\Media\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $path = $data['attachment'];
        $disk = 'public'; // Default disk

        // Get file details from storage
        $storage = Storage::disk($disk);
        $fullPath = $storage->path($path); // Absolute path if needed, or use Relative for Storage methods

        $mimeType = $storage->mimeType($path);
        $size = $storage->size($path);

        $parts = pathinfo($path);
        $directory = $parts['dirname'] === '.' ? '' : $parts['dirname'];
        $filename = $parts['filename'];
        $extension = $parts['extension'];

        $aggregateType = $this->getAggregateType($mimeType);

        // Prepare Media attributes
        $data['disk'] = $disk;
        $data['directory'] = $directory;
        $data['filename'] = $filename;
        $data['extension'] = $extension;
        $data['mime_type'] = $mimeType;
        $data['aggregate_type'] = $aggregateType;
        $data['size'] = $size;
        $data['metadata'] = [
            'original_filename' => $data['original_filename'] ?? $filename . '.' . $extension,
        ];

        // Clean up temporary fields
        unset($data['attachment']);
        unset($data['original_filename']);

        return $data;
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
