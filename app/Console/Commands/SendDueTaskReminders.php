<?php

namespace App\Console\Commands;

use App\Services\TaskReminderService;
use Illuminate\Console\Command;

class SendDueTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for tasks due within the next 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(TaskReminderService $reminderService)
    {
        $isDryRun = $this->option('dry-run');
        $now = now();
        $reminderWindow = $now->copy()->addHours(24);

        $tasks = $reminderService->getDueTasks($now, $reminderWindow);

        if ($tasks->isEmpty()) {
            $this->info('No tasks found that need reminders.');

            return self::SUCCESS;
        }

        $this->info("Found {$tasks->count()} task(s) that need reminders.");

        if ($isDryRun) {
            $this->table(['Task ID', 'Title', 'User', 'Due Date'], $tasks->map(fn ($t) => [
                $t->id,
                $t->title,
                $t->user->name,
                $t->due_date->format('Y-m-d H:i'),
            ]));
            $this->info('Dry run completed. No emails were sent.');

            return self::SUCCESS;
        }

        $sent = $failed = 0;

        foreach ($tasks as $task) {
            $reminderService->sendReminder($task) ? $sent++ : $failed++;
        }

        $this->info("Reminders processed: {$sent} sent, {$failed} failed.");

        return self::SUCCESS;
    }
}
