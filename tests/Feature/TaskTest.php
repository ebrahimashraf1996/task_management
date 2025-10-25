<?php

namespace Tests\Feature;

use App\Enums\Task\TaskPriorityEnum;
use App\Enums\Task\TaskStatusEnum;
use App\Enums\User\UserRoleEnum;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\PassportTestCase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use PassportTestCase, RefreshDatabase;

    // ==================== INDEX (GET /api/tasks) TEST CASES ====================

    /** @test */
    public function authenticated_user_can_view_their_tasks()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        Task::factory()->count(3)->create(['user_id' => $user->id]);
        Task::factory()->count(2)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'tasks' => [
                        '*' => ['id', 'title', 'description', 'due_date', 'status', 'priority', 'user'],
                    ],
                    'paginator',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Tasks List',
            ]);
    }

    /** @test */
    public function admin_can_view_all_tasks()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $user1 = User::factory()->create(['role' => UserRoleEnum::User]);
        $user2 = User::factory()->create(['role' => UserRoleEnum::User]);

        Task::factory()->count(3)->create(['user_id' => $user1->id]);
        Task::factory()->count(2)->create(['user_id' => $user2->id]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'tasks',
                    'paginator',
                ],
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_tasks()
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_filter_tasks_by_status()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatusEnum::Pending,
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatusEnum::Done,
        ]);

        $response = $this->getJson('/api/tasks?status='.TaskStatusEnum::Pending->value);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_filter_tasks_by_priority()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        Task::factory()->create([
            'user_id' => $user->id,
            'priority' => TaskPriorityEnum::High,
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'priority' => TaskPriorityEnum::Low,
        ]);

        $response = $this->getJson('/api/tasks?priority='.TaskPriorityEnum::High->value);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_filter_tasks_by_due_date_range()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => '2025-01-15',
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => '2025-02-15',
        ]);

        $response = $this->getJson('/api/tasks?due_from=2025-02-01&due_to=2025-02-28');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_sort_tasks_ascending()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        Task::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/tasks?sort=asc');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_sort_tasks_descending()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        Task::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/tasks?sort=desc');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_paginate_tasks()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        Task::factory()->count(25)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/tasks?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'tasks',
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

    /** @test */
    public function filter_validation_fails_for_invalid_status()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $response = $this->getJson('/api/tasks?status=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function filter_validation_fails_for_invalid_priority()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $response = $this->getJson('/api/tasks?priority=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function filter_validation_fails_for_invalid_due_date()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $response = $this->getJson('/api/tasks?due_from=invalid-date');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_from']);
    }

    /** @test */
    public function filter_validation_fails_for_invalid_sort()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $response = $this->getJson('/api/tasks?sort=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort']);
    }

    /** @test */
    public function filter_validation_fails_for_invalid_per_page()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $response = $this->getJson('/api/tasks?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    // ==================== STORE (POST /api/tasks) TEST CASES ====================

    /** @test */
    public function regular_user_can_create_task_for_themselves()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'task' => ['id', 'title', 'description', 'due_date', 'status', 'priority', 'user'],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Task Created Successfully',
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function admin_can_create_task_for_specific_user()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        $targetUser = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($admin);

        $taskData = [
            'title' => 'Admin Task',
            'description' => 'Task for specific user',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::InProgress->value,
            'priority' => TaskPriorityEnum::Medium->value,
            'user_id' => $targetUser->id,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task Created Successfully',
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Admin Task',
            'user_id' => $targetUser->id,
        ]);
    }

    /** @test */
    public function regular_user_cannot_create_task_for_another_user()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        $otherUser = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
            'user_id' => $otherUser->id,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function admin_cannot_create_task_for_admin_user()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        $targetAdmin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $taskData = [
            'title' => 'Admin Task',
            'description' => 'Task for admin',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
            'user_id' => $targetAdmin->id,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function create_task_fails_when_title_is_missing()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function create_task_fails_when_title_exceeds_255_characters()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => str_repeat('a', 256),
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function create_task_fails_when_description_is_missing()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    /** @test */
    public function create_task_fails_when_due_date_is_missing()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function create_task_fails_when_due_date_is_invalid()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => 'invalid-date',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function create_task_fails_when_status_is_missing()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function create_task_fails_when_status_is_invalid()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => 999,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function create_task_fails_when_priority_is_missing()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function create_task_fails_when_priority_is_invalid()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => 999,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function admin_create_task_fails_when_user_id_is_missing()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function admin_create_task_fails_when_user_id_does_not_exist()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        Passport::actingAs($admin);

        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
            'user_id' => 99999,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_task()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => '2025-12-31',
            'status' => TaskStatusEnum::Pending->value,
            'priority' => TaskPriorityEnum::High->value,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(401);
    }

    // ==================== UPDATE (PUT/PATCH /api/tasks/{id}) TEST CASES ====================

    /** @test */
    public function user_can_update_their_own_task()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'status' => TaskStatusEnum::Done->value,
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'task' => ['id', 'title', 'description', 'due_date', 'status', 'priority', 'user'],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Task Updated Successfully',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function admin_can_update_any_task()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($admin);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Admin Updated Title',
            'status' => TaskStatusEnum::InProgress->value,
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Admin Updated Title',
        ]);
    }

    /** @test */
    public function user_cannot_update_another_users_task()
    {
        $user1 = User::factory()->create(['role' => UserRoleEnum::User]);
        $user2 = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user1);

        $task = Task::factory()->create(['user_id' => $user2->id]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_partially_update_task()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
            'status' => TaskStatusEnum::Pending,
        ]);

        $updateData = [
            'status' => TaskStatusEnum::Done->value,
        ];

        $response = $this->patchJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Original Title',
            'status' => TaskStatusEnum::Done->value,
        ]);
    }

    /** @test */
    public function admin_can_reassign_task_to_another_user()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        $user1 = User::factory()->create(['role' => UserRoleEnum::User]);
        $user2 = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($admin);

        $task = Task::factory()->create(['user_id' => $user1->id]);

        $updateData = [
            'user_id' => $user2->id,
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $user2->id,
        ]);
    }

    /** @test */
    public function regular_user_cannot_reassign_task()
    {
        $user1 = User::factory()->create(['role' => UserRoleEnum::User]);
        $user2 = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user1);

        $task = Task::factory()->create(['user_id' => $user1->id]);

        $updateData = [
            'user_id' => $user2->id,
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function update_task_fails_when_title_exceeds_255_characters()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => str_repeat('a', 256),
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function update_task_fails_when_status_is_invalid()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'status' => 999,
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function update_task_fails_when_priority_is_invalid()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'priority' => 999,
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    /** @test */
    public function update_task_fails_when_due_date_is_invalid()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'due_date' => 'invalid-date',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_task()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(401);
    }

    // ==================== DELETE (DELETE /api/tasks/{id}) TEST CASES ====================

    /** @test */
    public function user_can_delete_their_own_task()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task Deleted Successfully',
            ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /** @test */
    public function admin_can_delete_any_task()
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::Admin]);
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($admin);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task Deleted Successfully',
            ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /** @test */
    public function user_cannot_delete_another_users_task()
    {
        $user1 = User::factory()->create(['role' => UserRoleEnum::User]);
        $user2 = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user1);

        $task = Task::factory()->create(['user_id' => $user2->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_task()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function delete_nonexistent_task_returns_error()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $response = $this->deleteJson('/api/tasks/99999');

        $response->assertStatus(404);
    }

    // ==================== ADDITIONAL EDGE CASES ====================

    /** @test */
    public function task_response_includes_user_relationship()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'tasks' => [
                        '*' => [
                            'user' => [
                                'id',
                                'name',
                                'email',
                                'role',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function task_enums_are_properly_formatted_in_response()
    {
        $user = User::factory()->create(['role' => UserRoleEnum::User]);
        Passport::actingAs($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatusEnum::InProgress,
            'priority' => TaskPriorityEnum::High,
        ]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'tasks' => [
                        '*' => [
                            'status' => ['key', 'label'],
                            'priority' => ['key', 'label'],
                        ],
                    ],
                ],
            ]);
    }
}
