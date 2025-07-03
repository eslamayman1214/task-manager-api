<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Task extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'is_reminder_sent',
        'user_id',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'due_date' => 'datetime',
        'is_reminder_sent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    /**
     * Scope a query to only include tasks due within a specific timeframe.
     */
    public function scopeDueWithin($query, $startTime, $endTime)
    {
        return $query->whereBetween('due_date', [$startTime, $endTime]);
    }

    /**
     * Scope a query to exclude completed tasks.
     */
    public function scopeNotCompleted($query)
    {
        return $query->where('status', '!=', TaskStatus::COMPLETED->value);
    }

    /**
     * Scope a query to only include tasks that haven't been reminded yet.
     */
    public function scopeNotReminded($query)
    {
        return $query->where('is_reminder_sent', false);
    }
}
