<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\TaxonomyController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });
    });

    Route::apiResource('taxonomies', TaxonomyController::class)->only(['index', 'show']);

    Route::get('configurations/fetch', [ConfigController::class, 'fetch'])->name('configurations.fetch');
    Route::apiResource('configurations', ConfigController::class)->only(['index', 'show', 'update']);

    Route::prefix('media')->name('media.')->group(function (): void {
        Route::post('upload', [MediaController::class, 'upload'])->name('upload');
        Route::get('{media}', [MediaController::class, 'show'])->name('show');
        Route::delete('{media}', [MediaController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('auth:sanctum')->get('/users/{user}',
        // Placeholder for user details
        fn (Request $request, User $user) => response()->json([
            'data' => [
                'type' => 'users',
                'id' => (string) $user->id,
                'attributes' => [
                    'email' => $user->email,
                    'full_name' => $user->name,
                ],
            ],
        ]));
});
