<?php

namespace Tests\Feature;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\PassportTestCase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use PassportTestCase, RefreshDatabase;

    // ==================== INDEX (GET /api/users) TEST CASES ====================

    /** @test */
    public function admin_can_view_all_users()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->count(5)->create(['role' => UserRoleEnum::User]);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'users' => [
                        '*' => ['id', 'name', 'email', 'role'],
                    ],
                    'paginator',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Users List',
            ]);
    }

    /** @test */
    public function regular_user_cannot_view_all_users()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $response = $this->getJson('/api/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_users()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_filter_users_by_name()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->create(['name' => 'John Doe', 'role' => UserRoleEnum::User]);
        User::factory()->create(['name' => 'Jane Smith', 'role' => UserRoleEnum::User]);

        $response = $this->getJson('/api/users?name=John Doe');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'John Doe']);
    }

    /** @test */
    public function admin_can_filter_users_by_email()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->create(['email' => 'john@example.com', 'role' => UserRoleEnum::User]);
        User::factory()->create(['email' => 'jane@example.com', 'role' => UserRoleEnum::User]);

        $response = $this->getJson('/api/users?email=john@example.com');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => 'john@example.com']);
    }

    /** @test */
    public function admin_can_filter_users_by_role()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->count(3)->create(['role' => UserRoleEnum::User]);
        User::factory()->count(2)->create(['role' => UserRoleEnum::Admin]);

        $response = $this->getJson('/api/users?role='.UserRoleEnum::Admin->value);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_sort_users_ascending()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->count(3)->create(['role' => UserRoleEnum::User]);

        $response = $this->getJson('/api/users?sort=asc');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_sort_users_descending()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->count(3)->create(['role' => UserRoleEnum::User]);

        $response = $this->getJson('/api/users?sort=desc');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_paginate_users()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->count(25)->create(['role' => UserRoleEnum::User]);

        $response = $this->getJson('/api/users?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'users',
                    'paginator' => [
                        'meta' => [
                            'current_page',
                            'per_page',
                            'total_items',
                            'total_pages',
                        ],
                        'links' => [
                            'first_page_url',
                            'prev_page_url',
                            'next_page_url',
                            'last_page_url',
                        ],
                    ],
                ],
            ]);
    }

    // ==================== STORE (POST /api/users) TEST CASES ====================

    /** @test */
    public function admin_can_create_new_user()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User->value,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User Created Successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);
    }

    /** @test */
    public function regular_user_cannot_create_new_user()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User->value,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(403);
    }

    /** @test */
    public function create_user_fails_when_name_is_missing()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $userData = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User->value,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function create_user_fails_when_name_exceeds_255_characters()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $userData = [
            'name' => str_repeat('a', 256),
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User->value,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function create_user_fails_when_email_is_missing()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $userData = [
            'name' => 'New User',
            'password' => 'password123',
            'role' => UserRoleEnum::User->value,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function create_user_fails_when_email_already_exists()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role' => UserRoleEnum::User->value,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function create_user_fails_when_password_is_missing()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => UserRoleEnum::User->value,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function create_user_fails_when_password_is_less_than_8_characters()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'short',
            'role' => UserRoleEnum::User->value,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function create_user_fails_when_role_is_missing()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function create_user_fails_when_role_is_invalid()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'invalid_role',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    // ==================== UPDATE (PUT/PATCH /api/users/{id}) TEST CASES ====================

    /** @test */
    public function admin_can_update_user()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user = User::factory()->create(['role' => UserRoleEnum::User]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User Updated Successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function admin_can_update_user_role()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user = User::factory()->create(['role' => UserRoleEnum::User]);

        $updateData = [
            'role' => UserRoleEnum::Admin->value,
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => UserRoleEnum::Admin->value,
        ]);
    }

    /** @test */
    public function admin_can_update_user_password()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user = User::factory()->create(['role' => UserRoleEnum::User]);

        $updateData = [
            'password' => 'newpassword123',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function admin_can_partially_update_user()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => UserRoleEnum::User,
        ]);

        $updateData = [
            'name' => 'Updated Name Only',
        ];

        $response = $this->patchJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name Only',
            'email' => 'original@example.com',
        ]);
    }

    /** @test */
    public function regular_user_cannot_update_user()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $targetUser = User::factory()->create(['role' => UserRoleEnum::User]);

        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->putJson("/api/users/{$targetUser->id}", $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function update_user_fails_when_email_already_exists()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create(['email' => 'user@example.com']);

        $updateData = [
            'email' => 'existing@example.com',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function update_user_fails_when_password_is_less_than_8_characters()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user = User::factory()->create(['role' => UserRoleEnum::User]);

        $updateData = [
            'password' => 'short',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function update_user_fails_when_role_is_invalid()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user = User::factory()->create(['role' => UserRoleEnum::User]);

        $updateData = [
            'role' => 'invalid_role',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function update_user_allows_keeping_same_email()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user = User::factory()->create(['email' => 'user@example.com']);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'user@example.com',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);
    }

    // ==================== DELETE (DELETE /api/users/{id}) TEST CASES ====================

    /** @test */
    public function admin_can_delete_user()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user = User::factory()->create(['role' => UserRoleEnum::User]);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User Deleted Successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /** @test */
    public function regular_user_cannot_delete_user()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $targetUser = User::factory()->create(['role' => UserRoleEnum::User]);

        $response = $this->deleteJson("/api/users/{$targetUser->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_user()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function delete_nonexistent_user_returns_error()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $response = $this->deleteJson('/api/users/99999');

        $response->assertStatus(404);
    }
}
