<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    public function __construct(protected MediaService $mediaService) {}

    public function destroy(Media $media): Response
    {
        $this->mediaService->delete($media);

        return response()->noContent();
    }

    public function show(Media $media): MediaResource
    {
        return new MediaResource($media);
    }

    public function upload(Request $request): MediaResource
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'directory' => ['nullable', 'string'],
            'disk' => ['nullable', 'string', 'in:public,s3,local'],
        ]);

        $media = $this->mediaService->upload(
            $request->file('file'),
            $request->input('directory', 'uploads'),
            $request->input('disk', 'public')
        );

        return new MediaResource($media);
    }
}
