<?php

namespace App\Repositories;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaskReminderRepository
{
    public function getTasksDueSoon(Carbon $start, Carbon $end): Collection
    {
        return Task::with('user')
            ->dueWithin($start, $end)
            ->notCompleted()
            ->notReminded()
            ->get();
    }
}
