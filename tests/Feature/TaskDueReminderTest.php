<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskDueReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_tasks_found_output()
    {
        Artisan::call('tasks:send-reminders');
        $this->assertStringContainsString('No tasks found', Artisan::output());
    }

    public function test_dry_run_displays_task_info_but_does_not_send_email()
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => now()->addHours(23),
            'is_reminder_sent' => false,
            'status' => TaskStatus::PENDING->value,
        ]);

        Artisan::call('tasks:send-reminders --dry-run');
        $output = Artisan::output();

        $this->assertStringContainsString('Found 1 task(s)', $output);
        $this->assertStringContainsString($task->title, $output);

        Notification::assertNothingSent();
    }

    public function test_valid_task_sends_notification_and_marks_as_reminded()
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => now()->addHours(12),
            'is_reminder_sent' => false,
            'status' => TaskStatus::PENDING->value,
        ]);

        Artisan::call('tasks:send-reminders');

        Notification::assertSentTo($user, TaskDueReminder::class);
        $this->assertTrue($task->fresh()->is_reminder_sent);
    }

    public function test_overdue_task_is_excluded_from_notifications()
    {
        Notification::fake();

        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => now()->subHours(1),
            'is_reminder_sent' => false,
            'status' => TaskStatus::PENDING->value,
        ]);

        Artisan::call('tasks:send-reminders');
        Notification::assertNothingSent();
    }

    public function test_completed_task_is_excluded()
    {
        Notification::fake();

        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => now()->addHours(10),
            'is_reminder_sent' => false,
            'status' => TaskStatus::COMPLETED->value,
        ]);

        Artisan::call('tasks:send-reminders');
        Notification::assertNothingSent();
    }

    public function test_already_reminded_task_is_excluded()
    {
        Notification::fake();

        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'due_date' => now()->addHours(10),
            'is_reminder_sent' => true,
            'status' => TaskStatus::PENDING->value,
        ]);

        Artisan::call('tasks:send-reminders');
        Notification::assertNothingSent();
    }
}
