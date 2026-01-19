<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'users',
            'id' => (string) $this->id,
            'attributes' => [
                'email' => $this->email,
                'full_name' => $this->name,
                'created_at' => $this->created_at->toISOString(),
            ],
            'links' => [
                'self' => url("/api/v1/users/{$this->id}"),
            ],
        ];
    }
}
