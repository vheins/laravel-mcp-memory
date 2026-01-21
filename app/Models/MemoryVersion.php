<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class MemoryVersion extends Model
{
    use HasFactory;
    use HasUuids;
    use Cachable;

    public $timestamps = false;

    protected $fillable = [
        'memory_id',
        'version_number',
        'content',
    ];

    /**
     * @return BelongsTo<Memory, $this>
     */
    public function memory(): BelongsTo
    {
        return $this->belongsTo(Memory::class);
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
