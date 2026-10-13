<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->string('email')->toString())
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        if ($user->company_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->company_id);
        }

        $deviceName = $request->string('device_name')->toString() ?: 'api-token';
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->load(['company.defaultCurrency', 'stocks']);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request): UserResource
    {
        $user = $request->user();
        if ($user->company_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->company_id);
        }
        $user->load(['company.defaultCurrency', 'stocks']);

        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }
}
