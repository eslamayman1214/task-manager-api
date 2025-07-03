<?php

use Illuminate\Support\Facades\Schedule;

// Schedule a command to send task reminders every hour
Schedule::command('tasks:send-reminders')
    ->hourly()
    ->withoutOverlapping(10) // Prevent overlapping executions
    ->runInBackground();  // work in the background without blocking the main process (work asynchronously)
