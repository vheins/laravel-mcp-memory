<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Media extends Model
{
    use HasFactory;
    use Cachable;

    protected $appends = [
        'url',
    ];

    protected $fillable = [
        'disk',
        'directory',
        'filename',
        'extension',
        'mime_type',
        'aggregate_type',
        'size',
        'metadata',
    ];

    /**
     * @return HasMany<MediaAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size' => 'integer',
        ];
    }

    protected function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->directory . '/' . $this->filename . '.' . $this->extension);
    }

    protected static function booted(): void
    {
        static::deleting(function (Media $media): void {
            if (Storage::disk($media->disk)->exists($media->directory . '/' . $media->filename . '.' . $media->extension)) {
                Storage::disk($media->disk)->delete($media->directory . '/' . $media->filename . '.' . $media->extension);
            }
        });
    }
}
