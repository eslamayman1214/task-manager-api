<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ListTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', new Enum(TaskStatus::class)],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date'],
            'sort_by' => ['nullable', Rule::in(['priority', 'due_date', 'created_at'])],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $from = $this->input('due_from');
            $to = $this->input('due_to');

            if ($from && $to && strtotime($from) > strtotime($to)) {
                $validator->errors()->add('due_from', 'The due_from date must be before or equal to due_to.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'status.enum' => 'The selected status is invalid.',
            'due_from.date' => 'The due from date must be a valid date.',
            'due_to.date' => 'The due to date must be a valid date.',
            'sort_by.in' => 'The sort by field must be one of: priority, due_date, created_at.',
        ];
    }
}
