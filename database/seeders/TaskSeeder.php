<?php

namespace Database\Seeders;

use App\Enums\Task\TaskPriorityEnum;
use App\Enums\Task\TaskStatusEnum;
use App\Enums\User\UserRoleEnum;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('role', UserRoleEnum::User)->first();

        if (! $user) {
            $this->command->warn('No user found. Please run UserSeeder first.');

            return;
        }

        Task::updateOrCreate(
            ['title' => 'Test Task'],
            [
                'user_id' => $user->id,
                'description' => 'This is a test task for seeding.',
                'status' => TaskStatusEnum::Pending,
                'priority' => TaskPriorityEnum::High,
                'due_date' => Carbon::now()->addDays(5),
            ]
        );

    }
}
