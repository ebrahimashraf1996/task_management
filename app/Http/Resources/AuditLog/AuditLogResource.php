<?php

namespace App\Http\Resources\AuditLog;

use App\Http\Resources\GeneralEnumResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'action' => GeneralEnumResource::make($this->action),
            'entity' => class_basename($this->entity),
            'entity_id' => $this->entity_id,
            'changes' => $this->changes,
            'created_at' => reformatDate($this->created_at),
        ];
    }
}
