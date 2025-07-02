<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use App\Util\HttpStatusCodeUtil;

class TaskController extends Controller
{
    public function __construct(protected TaskService $service) {}

    public function index(ListTasksRequest $request)
    {
        $tasks = $this->service->list($request->validated());

        return $this->response(TaskResource::collection($tasks), HttpStatusCodeUtil::OK);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->service->store($request->validated());

        return $this->response(new TaskResource($task), HttpStatusCodeUtil::CREATED);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $task = $this->service->updateStatus($task, $request->validated()['status']);

        return $this->response(new TaskResource($task), HttpStatusCodeUtil::OK, 'Task status updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->service->delete($task);

        return $this->response([], HttpStatusCodeUtil::OK, 'Task deleted successfully.');
    }
}
