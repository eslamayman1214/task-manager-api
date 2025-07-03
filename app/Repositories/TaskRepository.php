<?php

namespace App\Repositories;

use App\Filters\TaskFilter;
use App\Models\Task;

class TaskRepository
{
    public function __construct(protected TaskFilter $filter) {}

    public function getUserTasks(int $page, int $perPage, array $filters)
    {
        return $this->filter->apply(auth()->user()->tasks()->getQuery(), $filters)->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Task
    {
        return auth()->user()->tasks()->create($data)->refresh();
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

    public function searchUserTasks(int $page, int $perPage, string $term)
    {
        return Task::search($term)
            ->query(fn ($q) => $q->where('user_id', auth()->id()))
            ->paginate($perPage, 'page', $page);
    }
}
