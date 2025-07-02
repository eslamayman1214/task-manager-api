<?php

namespace App\Repositories;

use App\Models\Task;

class TaskRepository
{
    public function create(array $data): Task
    {
        return auth()->user()->tasks()->create($data);
    }

    public function updateStatus(Task $task, string $status): Task
    {
        $task->update(['status' => $status]);

        return $task;
    }
}
