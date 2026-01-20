<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

test('mcp token management component can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('manage-mcp-tokens')
        ->assertStatus(200)
        ->assertSee('MCP Access Tokens');
});

test('user can create an mcp token', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $token = $user->createToken('test-token', ['mcp:read', 'mcp:write']);

    expect($token)->toBeInstanceOf(\Laravel\Sanctum\NewAccessToken::class);
    expect($user->tokens)->toHaveCount(1);
    expect($user->tokens->first()->name)->toBe('test-token');
    expect($user->tokens->first()->abilities)->toBe(['mcp:read', 'mcp:write']);
});

test('mcp token can receive specific abilities', function () {
    $user = User::factory()->create();

    $token = $user->createToken('admin-token', ['mcp:admin']);

    expect($token->accessToken->can('mcp:admin'))->toBeTrue();
    expect($token->accessToken->can('mcp:read'))->toBeFalse();
});

test('user can revoke an mcp token', function () {
    $user = User::factory()->create();
    $user->createToken('revoke-me');

    expect($user->tokens)->toHaveCount(1);

    $user->tokens()->delete();

    expect($user->fresh()->tokens)->toHaveCount(0);
});

test('tokens are long lived by default unless configured otherwise', function () {
    // Sanctum tokens by default have expires_at as null unless configured.
    // We want to ensure that for our use case, we can create tokens that do not expire.
    // Using createToken with no expiration argument (which relies on config or default).
    // Note: createToken's 3rd arg is expiresAt.

    $user = User::factory()->create();
    // Explicitly passing null for expiresAt if the method signature supports it,
    // or relying on default. The standard createToken signature in HasApiTokens is:
    // createToken(string $name, array $abilities = ['*'], DateTimeInterface|null $expiresAt = null)

    $token = $user->createToken('long-lived', ['*'], null);

    expect($token->accessToken->expires_at)->toBeNull();
});
