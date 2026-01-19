<?php

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\postJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can upload a file', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = postJson(route('api.v1.media.upload'), [
        'file' => $file,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'filename',
                    'url',
                ],
            ],
        ]);

    $media = Media::first();
    expect($media)->not->toBeNull();
    expect($media->mime_type)->toBe('image/jpeg');

    Storage::disk('public')->assertExists('uploads/' . $media->filename . '.jpg');
});

it('can fetch media details', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $media = Media::create([
        'disk' => 'public',
        'directory' => 'uploads',
        'filename' => 'test-file',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
        'metadata' => ['original_filename' => 'old.jpg'],
    ]);

    $response = \Pest\Laravel\getJson(route('api.v1.media.show', $media));

    $response->assertStatus(200)
        ->assertJsonPath('data.id', (string) $media->id);
});

it('can delete media', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 100);
    $path = $file->storeAs('uploads', 'test-doc.pdf', 'public');

    $media = Media::create([
        'disk' => 'public',
        'directory' => 'uploads',
        'filename' => 'test-doc',
        'extension' => 'pdf',
        'mime_type' => 'application/pdf',
        'aggregate_type' => 'pdf',
        'size' => 1024,
        'metadata' => ['original_filename' => 'document.pdf'],
    ]);

    $response = deleteJson(route('api.v1.media.destroy', $media));

    $response->assertNoContent();

    expect(Media::count())->toBe(0);
    Storage::disk('public')->assertMissing($path);
});

it('validates upload size', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->create('large-video.mp4', 15000); // 15MB

    $response = postJson(route('api.v1.media.upload'), [
        'file' => $file,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});
