<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Todo;
use Carlxaeron\General\Models\GeneralMap;

class TodoGeneralMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_todo_with_general_map_relationship()
    {
        $user = User::factory()->create();
        
        $response = $this->postJson('/api/todos', [
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false,
            'user_id' => $user->id
        ]);

        $response->assertStatus(201);
        
        $todo = Todo::first();
        $this->assertNotNull($todo);
        $this->assertEquals('Test Todo', $todo->title);
        
        // Check that the general map relationship was created
        $generalMap = GeneralMap::where('mappable_type', User::class)
            ->where('mappable_id', $user->id)
            ->where('related_type', Todo::class)
            ->where('related_id', $todo->id)
            ->where('relationship_type', 'todo_owner')
            ->first();
            
        $this->assertNotNull($generalMap);
        $this->assertEquals('todo_owner', $generalMap->relationship_type);
    }

    public function test_user_can_get_their_todos_through_general_maps()
    {
        $user = User::factory()->create();
        
        // Create a todo and associate it with the user
        $todo = Todo::create([
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false
        ]);
        
        $user->addTodo($todo);
        
        // Get todos through the user model
        $userTodos = $user->getTodos();
        
        $this->assertCount(1, $userTodos);
        $this->assertEquals('Test Todo', $userTodos->first()->title);
    }

    public function test_todo_can_get_its_owner_through_general_maps()
    {
        $user = User::factory()->create();
        $todo = Todo::create([
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false
        ]);
        
        $user->addTodo($todo);
        
        // Get the owner through the todo model
        $owner = $todo->owner();
        
        $this->assertNotNull($owner);
        $this->assertEquals($user->id, $owner->id);
        $this->assertEquals($user->name, $owner->name);
    }

    public function test_user_can_assign_existing_todo_to_another_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $todo = Todo::create([
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false
        ]);
        
        // Initially assign to user1
        $user1->addTodo($todo);
        
        // Reassign to user2
        $response = $this->putJson("/api/todos/{$todo->id}", [
            'user_id' => $user2->id
        ]);
        
        $response->assertStatus(200);
        
        // Check that the relationship was updated
        $this->assertCount(0, $user1->getTodos());
        $this->assertCount(1, $user2->getTodos());
        
        $owner = $todo->owner();
        $this->assertEquals($user2->id, $owner->id);
    }

    public function test_user_can_assign_todo_to_another_user_via_assign_endpoint()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $todo = Todo::create([
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false
        ]);
        
        $response = $this->postJson("/api/todos/{$todo->id}/assign-user", [
            'user_id' => $user2->id,
            'metadata' => ['assigned_by' => 'test']
        ]);
        
        $response->assertStatus(200);
        
        $this->assertCount(1, $user2->getTodos());
        $owner = $todo->owner();
        $this->assertEquals($user2->id, $owner->id);
    }

    public function test_user_can_remove_todo_association()
    {
        $user = User::factory()->create();
        $todo = Todo::create([
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false
        ]);
        
        $user->addTodo($todo);
        
        $response = $this->deleteJson("/api/todos/{$todo->id}/remove-user", [
            'user_id' => $user->id
        ]);
        
        $response->assertStatus(200);
        
        $this->assertCount(0, $user->getTodos());
        $this->assertNull($todo->owner());
    }

    public function test_todo_listing_includes_owner_information()
    {
        $user = User::factory()->create();
        $todo = Todo::create([
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false
        ]);
        
        $user->addTodo($todo);
        
        $response = $this->getJson('/api/todos');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'description',
                'completed',
                'owner'
            ]
        ]);
        
        $todoData = $response->json()[0];
        $this->assertEquals($user->id, $todoData['owner']['id']);
        $this->assertEquals($user->name, $todoData['owner']['name']);
    }

    public function test_user_todos_endpoint_works_with_general_maps()
    {
        $user = User::factory()->create();
        
        $todo = Todo::create([
            'title' => 'Test Todo',
            'description' => 'Test Description',
            'completed' => false
        ]);
        
        $user->addTodo($todo);
        
        $response = $this->getJson("/api/users/{$user->id}/todos");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'todos' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'completed'
                ]
            ]
        ]);
        
        $this->assertCount(1, $response->json()['todos']);
    }
}
