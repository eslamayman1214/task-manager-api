<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(protected TaskRepository $repository) {}

    public function list(int $page, int $perPage, array $filters): LengthAwarePaginator
    {
        return $this->repository->getUserTasks($page, $perPage, $filters);
    }

    public function store(array $data): Task
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

    public function delete(Task $task): void
    {
        $this->repository->delete($task);
    }

    public function search(int $page, int $perPage, string $term): LengthAwarePaginator
    {
        return $this->repository->searchUserTasks($page, $perPage, $term);
    }
}
