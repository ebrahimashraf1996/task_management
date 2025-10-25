<?php

namespace Tests\Feature;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\PassportTestCase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use PassportTestCase, RefreshDatabase;

    // ==================== REGISTER API TEST CASES ====================

    /** @test */
    public function user_can_register_successfully_with_user_role()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User->value,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User Registered Successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'name' => 'Normal User',
            'role' => UserRoleEnum::User->value,
        ]);
    }

    /** @test */
    public function admin_can_register_successfully_with_admin_role()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::Admin->value,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User Registered Successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'name' => 'Admin User',
            'role' => UserRoleEnum::Admin->value,
        ]);
    }

    /** @test */
    public function register_fails_when_name_is_missing()
    {
        $response = $this->postJson('/api/auth/register', [
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function register_fails_when_name_exceeds_255_characters()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => str_repeat('a', 256),
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function register_fails_when_email_is_missing()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function register_fails_when_email_is_invalid_format()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function register_fails_when_email_already_exists()
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function register_fails_when_email_exceeds_255_characters()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => str_repeat('a', 250).'@test.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function register_fails_when_password_is_missing()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function register_fails_when_password_is_less_than_8_characters()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'pass',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function register_fails_when_role_is_missing()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function register_fails_when_role_is_invalid()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'invalid_role',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function register_returns_user_resource_and_token_on_success()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    // ==================== LOGIN API TEST CASES ====================

    /** @test */
    public function user_can_login_successfully_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRoleEnum::User,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User Logged In Successfully',
            ]);
    }

    /** @test */
    public function admin_can_login_successfully_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRoleEnum::Admin,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User Logged In Successfully',
            ]);
    }

    /** @test */
    public function login_fails_when_email_is_missing()
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_fails_when_password_is_missing()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function login_fails_when_user_does_not_exist()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment([
                'password' => ['These Credentials Do Not Match Our Records'],
            ]);
    }

    /** @test */
    public function login_fails_when_password_is_incorrect()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonFragment([
                'password' => ['These Credentials Do Not Match Our Records'],
            ]);
    }

    /** @test */
    public function login_fails_when_both_email_and_password_are_missing()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function login_returns_user_resource_and_token_on_success()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => UserRoleEnum::User,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function login_is_case_sensitive_for_email()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'User@Example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function multiple_users_can_login_with_different_credentials()
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password456'),
        ]);

        $response1 = $this->postJson('/api/auth/login', [
            'email' => 'user1@example.com',
            'password' => 'password123',
        ]);

        $response2 = $this->postJson('/api/auth/login', [
            'email' => 'user2@example.com',
            'password' => 'password456',
        ]);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $this->assertNotEquals(
            $response1->json('data.token'),
            $response2->json('data.token')
        );
    }
}
