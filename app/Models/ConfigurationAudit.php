<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_id',
        'actor_id',
        'old_value',
        'new_value',
    ];

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(Configuration::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
