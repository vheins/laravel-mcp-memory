<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });
    });

    Route::apiResource('taxonomies', \App\Http\Controllers\Api\TaxonomyController::class)->only(['index', 'show']);

    Route::get('configurations/fetch', [\App\Http\Controllers\Api\ConfigController::class, 'fetch'])->name('configurations.fetch');
    Route::apiResource('configurations', \App\Http\Controllers\Api\ConfigController::class)->only(['index', 'show', 'update']);

    Route::prefix('media')->name('media.')->group(function () {
        Route::post('upload', [\App\Http\Controllers\Api\MediaController::class, 'upload'])->name('upload');
        Route::get('{media}', [\App\Http\Controllers\Api\MediaController::class, 'show'])->name('show');
        Route::delete('{media}', [\App\Http\Controllers\Api\MediaController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('auth:sanctum')->post('mcp', \App\Http\Controllers\Api\MemoryMcpController::class)->name('mcp');

    Route::middleware('auth:sanctum')->get('/users/{user}', function (Request $request, \App\Models\User $user) {
        // Placeholder for user details
        return response()->json([
            'data' => [
                'type' => 'users',
                'id' => (string) $user->id,
                'attributes' => [
                    'email' => $user->email,
                    'full_name' => $user->name,
                ],
            ],
        ]);
    });
});
