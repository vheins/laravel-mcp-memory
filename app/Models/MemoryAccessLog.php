<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryAccessLog extends Model
{
    use HasFactory;
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'action',
        'query',
        'filters',
        'metadata',
        'result_count',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'metadata' => 'array',
            'result_count' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
