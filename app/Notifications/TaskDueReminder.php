<?php

namespace App\Notifications;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Task $task, protected ?Carbon $now = null)
    {
        // Set queue priority and delay if needed
        $this->onQueue('notifications');
        $this->now = $now ?? now();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $dueDate = $this->task->due_date;
        $hoursUntilDue = $this->now->diffInHours($this->task->due_date, false);
        $urgencyText = $hoursUntilDue <= 2 ? 'very soon' : 'within 24 hours';

        return (new MailMessage)
            ->subject("⏰ Task Reminder: \"{$this->task->title}\" is due {$urgencyText}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("This is a friendly reminder that your task \"{$this->task->title}\" is due {$urgencyText}.")
            ->line("**Due Date:** {$dueDate->format('l, F j, Y \a\t g:i A')}")
            ->when($this->task->description, function ($mail) {
                return $mail->line("**Description:** {$this->task->description}");
            })
            ->action('View Task Details', $this->taskUrl())
            ->line('Don\'t forget to mark it as complete once you\'re done!')
            ->salutation('Best regards, Your Task Management System');
    }

    protected function taskUrl(): string
    {
        return url("/tasks/{$this->task->id}");
    }
}
