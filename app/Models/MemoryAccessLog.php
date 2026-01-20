<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MemoryAccessLog extends Model
{
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

    protected $casts = [
        'filters' => 'array',
        'metadata' => 'array',
        'result_count' => 'integer',
        'created_at' => 'datetime',
    ];
}
