<?php

namespace Tests\Feature\Task;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_task_status_with_valid_transition()
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'status' => TaskStatus::PENDING->value,
        ]);

        $response = $this->actingAs($user)->patchJson("/api/v1/tasks/{$task->id}/status", [
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status.value', TaskStatus::IN_PROGRESS->value);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function test_user_cannot_jump_directly_to_completed()
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'status' => TaskStatus::PENDING->value,
        ]);

        $response = $this->actingAs($user)->patchJson("/api/v1/tasks/{$task->id}/status", [
            'status' => TaskStatus::COMPLETED->value,
        ]);

        $response->assertStatus(422)
            ->assertSee('Invalid status transition');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::PENDING->value,
        ]);
    }

    public function test_user_cannot_update_another_users_task()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create([
            'status' => TaskStatus::PENDING->value,
        ]);

        $response = $this->actingAs($user)->patchJson("/api/v1/tasks/{$task->id}/status", [
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);

        $response->assertForbidden(); // Because of the authorize() in UpdateTaskStatusRequest
    }
}
