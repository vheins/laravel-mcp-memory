<?php

use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'Super Admin']);
    $this->user->assignRole($role);
});

it('can render the index page', function () {
    $this->actingAs($this->user)
        ->get(MediaResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list media', function () {
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

    Livewire::test(ListMedia::class)
        ->assertCanSeeTableRecords([$media]);
});

it('can create media', function () {
    $file = UploadedFile::fake()->image('new-image.jpg');

    Livewire::test(CreateMedia::class)
        ->fillForm([
            'attachment' => $file,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $media = Media::first();
    expect($media)->not->toBeNull();
    // Verify filename matches what we expect from our logic (cleaned filename from upload)
    expect($media->filename)->not->toBe('new-image');
    expect($media->metadata['original_filename'])->toBe('new-image.jpg');
});

it('can delete media', function () {
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

    // Create physical file mock
    Storage::disk('public')->put('uploads/test-file.jpg', 'content');

    Livewire::test(ListMedia::class)
        ->callTableAction('delete', $media);

    expect(Media::count())->toBe(0);
    Storage::disk('public')->assertMissing('uploads/test-file.jpg');
});
