<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class TaskFilter
{
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['due_from'] ?? null, fn ($q, $from) => $q->whereDate('due_date', '>=', $from))
            ->when($filters['due_to'] ?? null, fn ($q, $to) => $q->whereDate('due_date', '<=', $to))
            ->when($filters['sort_by'] ?? null, function ($q, $sort) {
                return match ($sort) {
                    'priority' => $q->orderByRaw("FIELD(priority, 'high', 'medium', 'low')"),
                    'due_date' => $q->orderBy('due_date'),
                    'created_at' => $q->orderBy('created_at'),
                    default => $q->latest('id'),
                };
            }, fn ($q) => $q->latest('id'));
    }
}
