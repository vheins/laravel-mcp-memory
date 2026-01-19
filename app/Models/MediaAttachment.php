<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'entity_type',
        'entity_id',
        'tag',
    ];

    protected $casts = [
        'attached_at' => 'datetime',
    ];

    public function media(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function entity(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
