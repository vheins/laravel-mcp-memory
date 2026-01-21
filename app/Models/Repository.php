<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RepositoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Repository extends Model
{
    use HasFactory;
    use HasUuids;
    use Cachable;

    protected $fillable = [
        'slug',
        'name',
        'organization_id',
        'description',
        'is_active',
    ];

    public static function factory(): RepositoryFactory
    {
        return RepositoryFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
