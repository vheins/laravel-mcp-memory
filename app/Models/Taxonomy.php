<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Taxonomy extends Model
{
    use HasFactory;
    use Cachable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_hierarchical',
    ];

    /**
     * @return HasMany<Term, $this>
     */
    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    protected function casts(): array
    {
        return [
            'is_hierarchical' => 'boolean',
        ];
    }
}
