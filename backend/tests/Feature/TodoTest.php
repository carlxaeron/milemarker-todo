<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_todos()
    {
        $response = $this->get('/todos');
        $response->assertStatus(200);
    }

    public function test_can_create_todo()
    {
        $todoData = [
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false
        ];

        $response = $this->post('/todos', $todoData);
        $response->assertStatus(201);
        $response->assertJsonFragment(['title' => 'Test Todo']);
    }

    public function test_can_update_todo()
    {
        $todo = Todo::create([
            'title' => 'Original Title',
            'description' => 'Original Description',
            'completed' => false
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'completed' => true
        ];

        $response = $this->put("/todos/{$todo->id}", $updateData);
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Updated Title', 'completed' => true]);
    }

    public function test_can_delete_todo()
    {
        $todo = Todo::create([
            'title' => 'To Delete',
            'description' => 'Will be deleted',
            'completed' => false
        ]);

        $response = $this->delete("/todos/{$todo->id}");
        $response->assertStatus(204);
    }
}
