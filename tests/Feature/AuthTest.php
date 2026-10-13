<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCompanyUsers;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreatesCompanyUsers;
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token(): void
    {
        $user = $this->createAdminUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'email', 'company', 'roles', 'permissions'],
            ])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = $this->createAdminUser();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_fetch_me_and_logout(): void
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Déconnexion réussie.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }
}
