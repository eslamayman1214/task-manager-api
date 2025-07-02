<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date', 'after:now'],
            'priority' => ['required', new Enum(TaskPriority::class)],
            'status' => ['required', new Enum(TaskStatus::class)],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'description.string' => 'The description must be a string.',
            'due_date.required' => 'The due date field is required.',
            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after' => 'The due date must be a date after now.',
            'priority.required' => 'The priority field is required.',
            'priority.enum' => 'The selected priority is invalid.',
            'status.required' => 'The status field is required.',
            'status.enum' => 'The selected status is invalid.',
        ];
    }
}
