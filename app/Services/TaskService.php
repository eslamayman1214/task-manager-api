<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Repositories\TaskRepository;

class TaskService
{
    public function __construct(protected TaskRepository $repository) {}

    public function store(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateStatus(Task $task, string $newStatus): Task
    {
        $newStatusEnum = TaskStatus::from($newStatus);
        $currentStatusEnum = TaskStatus::from($task->status->value);

        if (! $currentStatusEnum->canTransitionTo($newStatusEnum)) {
            abort(422, 'Invalid status transition.');
        }

        return $this->repository->updateStatus($task, $newStatusEnum->value);
    }
}
