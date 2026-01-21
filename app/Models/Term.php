<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxonomy_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'order',
    ];

    /**
     * @return HasMany<Term, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Term::class, 'parent_id');
    }

    /**
     * @return BelongsTo<Term, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }

    /**
     * @return BelongsTo<Taxonomy, $this>
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /**
     * @return MorphToMany<User, $this, Pivot>
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'entity', 'entity_terms');
    }

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }
}
