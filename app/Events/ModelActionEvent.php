<?php

namespace App\Events;

use App\Enums\AuditLog\AuditLogActionEnum;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelActionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Model $model,
        public AuditLogActionEnum $action,
        public ?array $changes = null
    ) {}

}
