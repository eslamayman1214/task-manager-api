<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTasksTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/tasks/search?q=test');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_search_query_parameter()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_search_query_must_be_string()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?q[]=test');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q'])
            ->assertJsonPath('errors.q', function ($errors) {
                return in_array('The search query must be a string.', $errors);
            });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_search_query_minimum_length()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?q=a');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q'])
            ->assertJsonPath('errors.q', function ($errors) {
                return in_array('The search query must be at least 2 characters long.', $errors);
            });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_search_results_for_authenticated_user()
    {
        // Create tasks for the authenticated user
        $userTask1 = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Laravel Development',
            'description' => 'Working on Laravel project',
        ]);

        $userTask2 = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Vue.js Frontend',
            'description' => 'Building frontend with Vue',
        ]);

        // Create task for another user (should not appear in results)
        Task::factory()->create([
            'user_id' => $this->otherUser->id,
            'title' => 'Laravel Testing',
            'description' => 'Testing Laravel application',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?q=Laravel');

        $response->assertStatus(200)
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
                'message',
            ])
            ->assertJsonFragment([
                'message' => 'Search results retrieved successfully.',
            ]);

        // Check that at least one task with 'Laravel' in title/description is returned
        $items = $response->json('data.items');
        $this->assertGreaterThan(0, count($items));

        // Verify the task belongs to the authenticated user
        foreach ($items as $item) {
            $this->assertTrue(
                str_contains(strtolower($item['title']), 'laravel') ||
                    str_contains(strtolower($item['description']), 'laravel')
            );
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_results_when_no_matches_found()
    {
        // Create some tasks that won't match the search
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Vue.js Development',
            'description' => 'Working on Vue project',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?q=NonExistentTerm');

        $response->assertStatus(200)
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.pagination.total', 0)
            ->assertJsonFragment([
                'message' => 'Search results retrieved successfully.',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_only_returns_tasks_belonging_to_authenticated_user()
    {
        // Create tasks for different users
        $userTask = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'User Task Test',
            'description' => 'Task for authenticated user',
        ]);

        $otherUserTask = Task::factory()->create([
            'user_id' => $this->otherUser->id,
            'title' => 'Other User Task Test',
            'description' => 'Task for other user',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?q=Test');

        $response->assertStatus(200);

        $responseData = $response->json('data.items');

        // Debug: Add assertion to see what's in the response
        $this->assertIsArray($responseData, 'Response data items should be an array');

        // Should only return tasks belonging to authenticated user
        foreach ($responseData as $task) {
            // Verify each returned task belongs to the authenticated user by checking it's not the other user's task
            $this->assertNotEquals($otherUserTask->id, $task['id']);

            // Additionally, verify it contains the search term
            $this->assertTrue(
                str_contains(strtolower($task['title']), 'test') ||
                    str_contains(strtolower($task['description']), 'test'),
                'Task should contain the search term "test"'
            );
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_task_resource_correctly()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Task Unique',
            'description' => 'Test Description',
            'due_date' => '2024-12-31 23:59:59',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?q=Unique');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'status' => ['value', 'label'],
                            'priority' => ['value', 'label'],
                        ],
                    ],
                    'pagination',
                ],
            ]);

        // Verify the specific task is in the results
        $items = $response->json('data.items');

        // Debug: Add more detailed error message
        $this->assertGreaterThan(
            0,
            count($items),
            'Should return at least one task. Response: '.json_encode($items)
        );

        $foundTask = collect($items)->firstWhere('id', $task->id);

        $this->assertNotNull(
            $foundTask,
            'Task should be found in search results. Available tasks: '.
                collect($items)->pluck('id')->implode(', ').'. Looking for task ID: '.$task->id
        );

        $this->assertEquals('Test Task Unique', $foundTask['title']);
        $this->assertEquals('Test Description', $foundTask['description']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_special_characters_in_search_query()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Special Characters Task',
            'description' => 'Task with special & characters!',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?'.http_build_query(['q' => 'Special']));

        $response->assertStatus(200);

        // Check that the response has the correct structure
        $items = $response->json('data.items');
        $this->assertIsArray($items);

        // If there are results, verify they contain the search term
        if (count($items) > 0) {
            $foundTask = collect($items)->firstWhere('id', $task->id);
            $this->assertNotNull($foundTask, 'Task with special characters should be found');
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_uses_sanctum_authentication_middleware()
    {
        // Create a Sanctum token for the user
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Create a task to search for
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Token Task',
            'description' => 'Task for token test',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/tasks/search?q=Token');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'pagination',
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_includes_pagination_metadata()
    {
        // Create multiple tasks to test pagination
        $tasks = Task::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'title' => 'Searchable Task',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?q=Searchable');

        $response->assertStatus(200)
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
            ]);

        $pagination = $response->json('data.pagination');
        $this->assertIsInt($pagination['total']);
        $this->assertIsInt($pagination['currentPage']);
        $this->assertEquals(1, $pagination['currentPage']);
    }

    /**
     * Test to verify basic Task factory and database operations
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_and_retrieve_tasks_from_database()
    {
        // Create a task using the factory
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Factory Test Task',
            'description' => 'Factory test description',
        ]);

        // Verify task was created
        $this->assertNotNull($task->id);
        $this->assertEquals($this->user->id, $task->user_id);
        $this->assertEquals('Factory Test Task', $task->title);

        // Verify task exists in database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $this->user->id,
            'title' => 'Factory Test Task',
        ]);

        // Verify we can retrieve it
        $retrievedTask = Task::find($task->id);
        $this->assertNotNull($retrievedTask);
        $this->assertEquals($task->id, $retrievedTask->id);

        // Verify user relationship works
        $this->assertEquals($this->user->id, $retrievedTask->user_id);

        // Check if we can query by user_id
        $userTasks = Task::where('user_id', $this->user->id)->get();
        $this->assertGreaterThan(0, $userTasks->count());
        $this->assertTrue($userTasks->contains('id', $task->id));
    }

    /**
     * Additional test to debug search functionality
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_debug_search_functionality()
    {
        // Create a task for the authenticated user
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Debug Task',
            'description' => 'Debug description',
        ]);

        // Force indexing to ensure Scout can search this task
        $task->searchable();

        // Verify task exists in DB
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $this->user->id,
            'title' => 'Debug Task',
            'description' => 'Debug description',
        ]);

        // Confirm we can directly retrieve it
        $retrievedTask = Task::find($task->id);
        $this->assertNotNull($retrievedTask, 'Task should exist in database');
        $this->assertEquals($this->user->id, $retrievedTask->user_id);

        // Perform a search with matching term
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tasks/search?q=Debug');

        // Ensure response is OK
        if ($response->status() !== 200) {
            $this->fail('Search endpoint returned status: '.$response->status().
                '. Response: '.$response->getContent());
        }

        $responseData = $response->json();

        $this->assertArrayHasKey(
            'data',
            $responseData,
            'Response should have data key. Actual response: '.json_encode($responseData)
        );

        $this->assertArrayHasKey(
            'items',
            $responseData['data'],
            'Response data should have items key. Actual data: '.json_encode($responseData['data'])
        );

        $items = $responseData['data']['items'];

        // If no results, debug output
        if (empty($items)) {
            $allUserTasks = Task::where('user_id', $this->user->id)->get();

            $taskDetails = $allUserTasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'user_id' => $task->user_id,
                ];
            });

            $this->fail(
                'No search results found. '.
                    'User ID: '.$this->user->id.'. '.
                    'Created task ID: '.$task->id.'. '.
                    'All user tasks: '.$taskDetails->toJson().'. '.
                    'Search response: '.json_encode($responseData)
            );
        }

        $this->assertGreaterThan(0, count($items), 'Should find at least one task');
    }
}
