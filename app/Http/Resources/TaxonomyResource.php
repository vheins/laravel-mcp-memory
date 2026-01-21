<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxonomyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'taxonomies',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'is_hierarchical' => $this->is_hierarchical,
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
            ],
            'relationships' => [
                'terms' => [
                    'data' => TermResource::collection($this->whenLoaded('terms')),
                ],
            ],
            'links' => [
                'self' => route('api.v1.taxonomies.show', $this->id),
            ],
        ];
    }
}
