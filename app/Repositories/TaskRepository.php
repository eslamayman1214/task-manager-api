<?php

namespace App\Repositories;

use App\Filters\TaskFilter;
use App\Models\Task;

class TaskRepository
{
    public function __construct(protected TaskFilter $filter) {}

    public function getUserTasks(array $filters)
    {
        return $this->filter->apply(auth()->user()->tasks()->getQuery(), $filters)->get();
    }

    public function create(array $data): Task
    {
        return auth()->user()->tasks()->create($data);
    }

    public function updateStatus(Task $task, string $status): Task
    {
        $task->update(['status' => $status]);

        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
