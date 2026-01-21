<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class ConfigurationAudit extends Model
{
    use HasFactory;
    use Cachable;

    protected $fillable = [
        'configuration_id',
        'actor_id',
        'old_value',
        'new_value',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return BelongsTo<Configuration, $this>
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(Configuration::class);
    }
}
