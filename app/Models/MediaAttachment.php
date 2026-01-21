<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class MediaAttachment extends Model
{
    use HasFactory;
    use Cachable;

    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'entity_type',
        'entity_id',
        'tag',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    protected function casts(): array
    {
        return [
            'attached_at' => 'datetime',
        ];
    }
}
