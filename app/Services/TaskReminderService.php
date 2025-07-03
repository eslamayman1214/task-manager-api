<?php

namespace App\Services;

use App\Models\Task;
use App\Notifications\TaskDueReminder;
use App\Repositories\TaskReminderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TaskReminderService
{
    public function __construct(protected TaskReminderRepository $repository) {}

    public function getDueTasks(Carbon $start, Carbon $end)
    {
        return $this->repository->getTasksDueSoon($start, $end);
    }

    public function sendReminder(Task $task): bool
    {
        try {
            $task->user->notify(new TaskDueReminder($task));
            $task->update(['is_reminder_sent' => true]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send task reminder', [
                'task_id' => $task->id,
                'user_id' => $task->user_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
