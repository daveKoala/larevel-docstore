<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_tenant()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], [
            'X-Tenant-Id' => 'WayneEnt',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'tenant',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Verify tenant is included in response
        $this->assertEquals('WayneEnt', $response->json('data.tenant'));
    }

    public function test_user_can_login_with_tenant()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ], [
            'X-Tenant-Id' => 'WayneEnt',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'tenant',
                    'token_abilities',
                ],
            ]);

        // Verify tenant is in response
        $this->assertEquals('WayneEnt', $response->json('data.tenant'));

        // Verify token has tenant ability
        $abilities = $response->json('data.token_abilities');
        $this->assertContains('tenant:WayneEnt', $abilities);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_access_protected_route()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_route()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_token_includes_tenant_in_abilities()
    {
        $user = User::factory()->create();

        // Simulate request with tenant header
        $response = $this->withHeaders([
            'X-Tenant-Id' => 'AcMe',
        ])->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $response->json('data.token');

        // Use the token to check abilities
        $checkResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tokens');

        $checkResponse->assertStatus(200);

        $tokens = $checkResponse->json('data.tokens');
        $this->assertNotEmpty($tokens);

        // Verify tenant ability exists
        $firstToken = $tokens[0];
        $this->assertContains('tenant:AcMe', $firstToken['abilities']);
    }
}
