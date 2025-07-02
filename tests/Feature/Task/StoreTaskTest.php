<?php

namespace Tests\Feature\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_task()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Test Task',
            'description' => 'Test task description',
            'due_date' => now()->addDays(2)->toDateTimeString(),
            'priority' => TaskPriority::MEDIUM->value,
            'status' => TaskStatus::PENDING->value,
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'due_date',
                    'priority',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_create_task()
    {
        $payload = [
            'title' => 'Unauthorized Task',
            'due_date' => now()->addDays(2)->toDateTimeString(),
            'priority' => TaskPriority::MEDIUM->value,
            'status' => TaskStatus::PENDING->value,
        ];

        $this->postJson('/api/v1/tasks', $payload)
            ->assertUnauthorized();
    }

    public function test_validation_errors_on_invalid_data()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => '',
            'due_date' => now()->subDay()->toDateTimeString(), // invalid: past date
            'priority' => 'invalid_priority',
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'due_date',
                'priority',
                'status',
            ]);
    }

    public function test_task_invalid_priority()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Invalid Priority Task',
            'due_date' => now()->addDays(2)->toDateTimeString(),
            'priority' => 'invalid_priority', // invalid priority
            'status' => TaskStatus::PENDING->value,
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_task_invalid_status()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Invalid Status Task',
            'due_date' => now()->addDays(2)->toDateTimeString(),
            'priority' => TaskPriority::MEDIUM->value,
            'status' => 'invalid_status', // invalid status
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
