<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfigurationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'configurations',
            'id' => (string) $this->id,
            'attributes' => [
                'key' => $this->key,
                'value' => $this->value,
                'type' => $this->type,
                'group' => $this->group,
                'is_public' => $this->is_public,
                'is_system' => $this->is_system,
                'updated_at' => $this->updated_at->toISOString(),
            ],
            'links' => [
                'self' => route('api.v1.configurations.show', $this->id),
            ],
        ];
    }
}
