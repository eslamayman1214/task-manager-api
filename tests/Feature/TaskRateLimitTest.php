<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class TaskRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('tasks-create|'.'127.0.0.1');  // Clear rate limit for testing and 127.0.0.1 is default IP for local testing
    }

    public function test_task_creation_rate_limit_is_enforced()
    {
        $user = User::factory()->create();

        // Hit the endpoint 5 times (limit is 5 per minute)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($user)->postJson('/api/v1/tasks', [
                'title' => 'Test Task',
                'description' => 'Description',
                'due_date' => now()->addDay()->toDateTimeString(),
                'priority' => 'medium',
                'status' => 'pending',
            ]);
            $response->assertStatus(201);
        }

        // 6th request should be rate limited
        $response = $this->actingAs($user)->postJson('/api/v1/tasks', [
            'title' => 'Test Task 6',
            'description' => 'Description',
            'due_date' => now()->addDay()->toDateTimeString(),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'message' => 'Too many requests. Please slow down.',
        ]);
    }
}
