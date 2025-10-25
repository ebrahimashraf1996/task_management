<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\GeneralEnumResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => reformatDate($this->due_date),
            'status' => GeneralEnumResource::make($this->status),
            'priority' => GeneralEnumResource::make($this->priority),
            'user' => UserResource::make($this->user),
        ];
    }
}
