<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
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

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
    ];

    protected $appends = [
        'url',
    ];

    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->directory.'/'.$this->filename.'.'.$this->extension);
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Media $media) {
            if (\Illuminate\Support\Facades\Storage::disk($media->disk)->exists($media->directory.'/'.$media->filename.'.'.$media->extension)) {
                \Illuminate\Support\Facades\Storage::disk($media->disk)->delete($media->directory.'/'.$media->filename.'.'.$media->extension);
            }
        });
    }
}
