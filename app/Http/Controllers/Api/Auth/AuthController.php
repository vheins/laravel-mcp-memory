<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthSessionResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $attributes = $request->input('data.attributes');

        $user = User::create([
            'name' => $attributes['full_name'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']),
        ]);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201)
            ->header('Content-Type', 'application/vnd.api+json');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $attributes = $request->input('data.attributes');

        $user = User::where('email', $attributes['email'])->first();

        if (! $user || ! Hash::check($attributes['password'], $user->password)) {
            throw ValidationException::withMessages([
                'data.attributes.email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return (new AuthSessionResource([
            'id' => 'current',
            'access_token' => $token,
        ]))
            ->response()
            ->setStatusCode(200)
            ->header('Content-Type', 'application/vnd.api+json');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    public function me(Request $request): JsonResponse
    {
        return (new UserResource($request->user()))
            ->response()
            ->setStatusCode(200)
            ->header('Content-Type', 'application/vnd.api+json');
    }

    public function forgotPassword(Request $request)
    {
        // To be implemented
        return response()->json(['meta' => ['message' => 'Not implemented yet']], 501);
    }

    public function resetPassword(Request $request)
    {
        // To be implemented
        return response()->json(['meta' => ['message' => 'Not implemented yet']], 501);
    }

    public function changePassword(Request $request)
    {
        // To be implemented
        return response()->json(['meta' => ['message' => 'Not implemented yet']], 501);
    }
}
