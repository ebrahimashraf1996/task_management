<?php

namespace App\Observers;

use App\Enums\AuditLog\AuditLogActionEnum;
use App\Events\ModelActionEvent;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        event(new ModelActionEvent($model, AuditLogActionEnum::Create));

    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $changes = [
            'old' => $model->getOriginal(),
            'new' => $model->getChanges(),
        ];

        event(new ModelActionEvent($model, AuditLogActionEnum::Update, $changes));

    }

    /**
     * Handle the Model "deleting" event.
     */
    public function deleting(Model $model): void
    {
        event(new ModelActionEvent($model, AuditLogActionEnum::Delete));
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        //
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        //
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        //
    }
}
