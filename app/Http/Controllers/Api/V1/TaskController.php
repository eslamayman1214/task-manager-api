<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use App\Util\HttpStatusCodeUtil;

class TaskController extends Controller
{
    public function __construct(protected TaskService $service) {}

    public function store(StoreTaskRequest $request)
    {
        $task = $this->service->store($request->validated());

        return $this->response(new TaskResource($task), HttpStatusCodeUtil::CREATED);
    }
}
