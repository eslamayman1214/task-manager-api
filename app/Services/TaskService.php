<?php

namespace App\Services;

use App\Repositories\TaskRepository;

class TaskService
{
    public function __construct(protected TaskRepository $repository) {}

    public function store(array $data)
    {
        return $this->repository->create($data);
    }
}
