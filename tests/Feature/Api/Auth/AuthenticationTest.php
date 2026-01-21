<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('users can register', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'data' => [
            'type' => 'auth_register',
            'attributes' => [
                'email' => 'test@example.com',
                'password' => 'Password123!',
                'full_name' => 'Test User',
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.attributes.email', 'test@example.com');

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('users can login', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'data' => [
            'type' => 'auth_login',
            'attributes' => [
                'email' => 'test@example.com',
                'password' => 'Password123!',
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'attributes' => [
                    'access_token',
                    'token_type',
                ],
            ],
        ]);
});

test('users cannot login with invalid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'data' => [
            'type' => 'auth_login',
            'attributes' => [
                'email' => 'test@example.com',
                'password' => 'WrongPassword',
            ],
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data.attributes.email']);
});

test('authenticated users can get their profile', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJsonPath('data.id', (string) $user->id)
        ->assertJsonPath('data.attributes.email', $user->email);
});

test('authenticated users can logout', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['*']);

    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertNoContent();
});
