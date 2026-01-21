<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoryAuditLog extends Model
{
    use HasFactory;
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'memory_id',
        'actor_id',
        'actor_type',
        'event',
        'old_value',
        'new_value',
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
            'old_value' => 'array',
            'new_value' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
