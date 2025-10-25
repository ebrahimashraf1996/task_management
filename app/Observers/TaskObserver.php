<?php

namespace App\Observers;

use App\Enums\Task\TaskStatusEnum;
use App\Mail\TaskCreatedMail;
use App\Mail\TaskDoneMail;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        if ($task->user && $task->user->email) {

            Mail::to($task->user->email)->send(new TaskCreatedMail($task));

        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        if ($task->isDirty('status') && $task->status === TaskStatusEnum::Done) {

            if ($task->user && $task->user->email) {

                Mail::to($task->user->email)->send(new TaskDoneMail($task));

            }
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
