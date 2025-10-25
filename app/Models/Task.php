<?php

namespace App\Models;

use App\Enums\Task\TaskPriorityEnum;
use App\Enums\Task\TaskStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description', 'status', 'priority', 'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => TaskStatusEnum::class,
        'priority' => TaskPriorityEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFullTextSearch($q, $text): Builder
    {
        return $q->whereRaw('MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE)', [$text]);
    }
}
