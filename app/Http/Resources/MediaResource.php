<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'media',
            'id' => (string) $this->id,
            'attributes' => [
                'filename' => $this->filename,
                'extension' => $this->extension,
                'mime_type' => $this->mime_type,
                'aggregate_type' => $this->aggregate_type,
                'size' => $this->size,
                'url' => $this->url,
                'created_at' => $this->created_at,
            ],
            'links' => [
                'self' => route('api.v1.media.show', $this->id, false), // Assuming we have show route, or just omit if not needed
            ],
        ];
    }
}
