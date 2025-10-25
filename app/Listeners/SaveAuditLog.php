<?php

namespace App\Listeners;

use App\Events\ModelActionEvent;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class SaveAuditLog implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(protected AuditLogService $service) {}

    /**
     * Handle the event.
     */
    public function handle(ModelActionEvent $event): void
    {
        $data = [
            'user_id' => Auth::id(),
            'action' => $event->action->value,
            'entity' => get_class($event->model),
            'entity_id' => $event->model->getKey(),
            'changes' => $event->changes ?? null,
        ];

        $this->service->createAuditLog($data);

    }
}
