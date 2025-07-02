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

        $response->assertOk()
            ->assertJsonCount(3, 'data.items')
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'due_date',
                            'status' => ['value', 'label'],
                            'priority' => ['value', 'label'],
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'pagination' => [
                        'total',
                        'perPage',
                        'currentPage',
                        'nextPage',
                        'previousPage',
                    ],
                ],
            ])
            ->assertJsonPath('data.pagination.total', 3);
    }

    public function test_filter_by_status()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['status' => TaskStatus::PENDING]);
        Task::factory()->for($user)->create(['status' => TaskStatus::IN_PROGRESS]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?status=pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.status.value', 'pending');
    }

    public function test_filter_by_due_from()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['due_date' => now()->addDays(1)]);
        Task::factory()->for($user)->create(['due_date' => now()->addDays(5)]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?due_from='.now()->addDays(3)->toDateString());

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_filter_by_due_to()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['due_date' => now()->addDays(1)]);
        Task::factory()->for($user)->create(['due_date' => now()->addDays(5)]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?due_to='.now()->addDays(3)->toDateString());

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_filter_by_due_from_and_due_to()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['due_date' => now()->addDays(1)]);
        Task::factory()->for($user)->create(['due_date' => now()->addDays(5)]);
        Task::factory()->for($user)->create(['due_date' => now()->addDays(10)]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?due_from='.now()->addDays(2)->toDateString().'&due_to='.now()->addDays(6)->toDateString());

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.pagination.total', 1);
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

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_pagination_structure_is_correct()
    {
        $user = User::factory()->create();
        Task::factory()->count(15)->for($user)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'pagination' => [
                        'total',
                        'perPage',
                        'currentPage',
                        'nextPage',
                        'previousPage',
                    ],
                ],
            ])
            ->assertJsonPath('data.pagination.total', 15)
            ->assertJsonPath('data.pagination.perPage', 10)
            ->assertJsonPath('data.pagination.currentPage', 1)
            ->assertJsonPath('data.pagination.nextPage', 2)
            ->assertJsonPath('data.pagination.previousPage', null);
    }

    public function test_empty_results_return_proper_structure()
    {
        $user = User::factory()->create();
        // No tasks created

        $response = $this->actingAs($user)->getJson('/api/v1/tasks');

        $response->assertOk()
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonPath('data.pagination.currentPage', 1)
            ->assertJsonPath('data.pagination.nextPage', null)
            ->assertJsonPath('data.pagination.previousPage', null);
    }

    public function test_second_page_pagination()
    {
        $user = User::factory()->create();
        Task::factory()->count(25)->for($user)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?page=2&per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data.items')
            ->assertJsonPath('data.pagination.total', 25)
            ->assertJsonPath('data.pagination.perPage', 10)
            ->assertJsonPath('data.pagination.currentPage', 2)
            ->assertJsonPath('data.pagination.nextPage', 3)
            ->assertJsonPath('data.pagination.previousPage', 1);
    }
}
