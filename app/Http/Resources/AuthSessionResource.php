<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'auth_session',
            'id' => $this->resource['id'] ?? 'current',
            'attributes' => [
                'access_token' => $this->resource['access_token'],
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration'), // Optional: Add expiration if configured
            ],
            'links' => [
                'self' => url('/api/v1/auth/session'),
            ],
        ];
    }
}
