<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\SearchTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use App\Util\HttpStatusCodeUtil;
use App\Util\PaginationUtil;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Task Management API",
 *     description="API for managing tasks"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Task API Server"
 * )
 */
class TaskController extends Controller
{
    public function __construct(protected TaskService $service) {}

    /**
     * @OA\Get(
     *     path="/api/v1/tasks",
     *     summary="List all tasks with optional filters",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="due_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="due_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string", enum={"priority", "due_date", "created_at"})),
     *
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index(ListTasksRequest $request)
    {
        $paginator = $this->service->list($request->get('page', PaginationUtil::PAGE), $request->get('per_page', PaginationUtil::LIMIT), $request->validated());

        $resourceData = TaskResource::collection($paginator);

        return $this->response($this->formatPaginationData($resourceData, $paginator), HttpStatusCodeUtil::OK);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks",
     *     summary="Create a new task",
     *     description="Allows users to create tasks. Limited to 5 requests per minute per user.",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"title", "due_date", "priority", "status"},
     *
     *             @OA\Property(property="title", type="string", example="Build API"),
     *             @OA\Property(property="description", type="string", example="Create endpoints and Swagger docs"),
     *             @OA\Property(property="due_date", type="string", format="date-time", example="2025-08-01T12:00:00Z"),
     *             @OA\Property(property="priority", type="string", example="high"),
     *             @OA\Property(property="status", type="string", example="pending")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Task created successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=429, description="Too many requests")
     * )
     */
    public function store(StoreTaskRequest $request)
    {
        $task = $this->service->store($request->validated());

        return $this->response(new TaskResource($task), HttpStatusCodeUtil::CREATED);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/tasks/{task}/status",
     *     summary="Update the status of a task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"status"},
     *
     *             @OA\Property(property="status", type="string", example="in_progress")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Task status updated"),
     *     @OA\Response(response=422, description="Invalid status transition")
     * )
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $task = $this->service->updateStatus($task, $request->validated()['status']);

        return $this->response(new TaskResource($task), HttpStatusCodeUtil::OK, 'Task status updated successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tasks/{task}",
     *     summary="Soft delete a task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Task deleted")
     * )
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->service->delete($task);

        return $this->response([], HttpStatusCodeUtil::OK, 'Task deleted successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tasks/search",
     *     summary="Search tasks by keyword",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="q", in="query", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Search results")
     * )
     */
    public function search(SearchTasksRequest $request)
    {
        $paginator = $this->service->search($request->get('page', PaginationUtil::PAGE), $request->get('per_page', PaginationUtil::LIMIT), $request->validated()['q']);
        $resourceData = TaskResource::collection($paginator);

        return $this->response($this->formatPaginationData($resourceData, $paginator), HttpStatusCodeUtil::OK, 'Search results retrieved successfully.');
    }
}
