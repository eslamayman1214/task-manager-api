<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_soft_delete_own_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Task deleted successfully.']);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_delete_others_task()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_guest_cannot_delete_task()
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertUnauthorized();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
