<?php

namespace Tests\Feature\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_tasks_with_no_filters()
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->for($user)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/tasks');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_filter_by_status()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['status' => TaskStatus::PENDING]);
        Task::factory()->for($user)->create(['status' => TaskStatus::IN_PROGRESS]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?status=pending');
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_filter_by_due_from()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['due_date' => now()->addDays(1)]);
        Task::factory()->for($user)->create(['due_date' => now()->addDays(5)]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?due_from='.now()->addDays(3)->toDateString());
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_filter_by_due_to()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['due_date' => now()->addDays(1)]);
        Task::factory()->for($user)->create(['due_date' => now()->addDays(5)]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?due_to='.now()->addDays(3)->toDateString());
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_filter_by_due_from_and_due_to()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['due_date' => now()->addDays(1)]);
        Task::factory()->for($user)->create(['due_date' => now()->addDays(5)]);
        Task::factory()->for($user)->create(['due_date' => now()->addDays(10)]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?due_from='.now()->addDays(2)->toDateString().'&due_to='.now()->addDays(6)->toDateString());
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_invalid_sort_by_value()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/api/v1/tasks?sort_by=random');
        $response->assertStatus(422)->assertJsonValidationErrors('sort_by');
    }

    public function test_invalid_status_value()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/api/v1/tasks?status=archived');
        $response->assertStatus(422)->assertJsonValidationErrors('status');
    }

    public function test_due_from_after_due_to_should_fail()
    {
        $user = User::factory()->create();
        $from = now()->addDays(5)->toDateString();
        $to = now()->addDays(2)->toDateString();

        $response = $this->actingAs($user)->getJson("/api/v1/tasks?due_from={$from}&due_to={$to}");
        $response->assertStatus(422)->assertJsonValidationErrors('due_from');
    }

    public function test_valid_full_filter_combination()
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'status' => TaskStatus::IN_PROGRESS,
            'due_date' => now()->addDays(5),
            'priority' => TaskPriority::HIGH,
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?status=in_progress&due_from='.now()->toDateString().'&due_to='.now()->addDays(6)->toDateString().'&sort_by=priority');
        $response->assertOk()->assertJsonCount(1, 'data');
    }
}
