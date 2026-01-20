<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoryAuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'memory_id',
        'actor_id',
        'actor_type',
        'event',
        'old_value',
        'new_value',
    ];

    public $timestamps = false;

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];

    public function memory(): BelongsTo
    {
        return $this->belongsTo(Memory::class);
    }
}
