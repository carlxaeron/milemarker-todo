<?php

namespace App\Http\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $todos = Todo::with('generalMaps')->orderBy('created_at', 'desc')->get();
        
        // Add owner information to each todo
        foreach ($todos as $todo) {
            $todo->owner = $todo->owner();
        }
        
        return response()->json($todos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
            'user_id' => 'required|exists:users,id'
        ]);

        $todo = Todo::create([
            'title' => $request->title,
            'description' => $request->description,
            'completed' => $request->completed ?? false
        ]);

        // Associate the todo with the user through general maps
        $user = User::findOrFail($request->user_id);
        $user->addTodo($todo, [
            'assigned_at' => now()->toISOString(),
            'assigned_by' => 'api'
        ]);

        // Load the owner information
        $todo->owner = $todo->owner();
        
        return response()->json($todo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $todo = Todo::with('generalMaps')->findOrFail($id);
        $todo->owner = $todo->owner();
        
        return response()->json($todo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
            'user_id' => 'sometimes|exists:users,id'
        ]);

        $todo = Todo::findOrFail($id);
        $todo->update($request->only(['title', 'description', 'completed']));
        
        // Handle user reassignment if user_id is provided
        if ($request->has('user_id')) {
            // Remove existing user association
            $currentOwner = $todo->owner();
            if ($currentOwner) {
                $currentOwner->removeTodo($todo);
            }
            
            // Add new user association
            $newUser = User::findOrFail($request->user_id);
            $newUser->addTodo($todo, [
                'assigned_at' => now()->toISOString(),
                'assigned_by' => 'api',
                'reassigned' => true
            ]);
        }
        
        // Load the updated owner information
        $todo->owner = $todo->owner();
        
        return response()->json($todo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);
        
        // Remove all user associations before deleting the todo
        $todo->generalMaps()->delete();
        
        $todo->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Associate a todo with a user.
     */
    public function assignUser(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'metadata' => 'nullable|array'
        ]);

        $todo = Todo::findOrFail($id);
        $user = User::findOrFail($request->user_id);
        
        // Check if already assigned using the package helper
        if ($user->hasRelatedModel($todo, 'todo_owner')) {
            return response()->json([
                'message' => 'Todo is already assigned to this user'
            ], 400);
        }
        
        $user->addTodo($todo, $request->metadata ?? []);
        
        return response()->json([
            'message' => 'Todo assigned successfully',
            'todo' => $todo->fresh(['generalMaps'])
        ]);
    }

    /**
     * Remove user association from a todo.
     */
    public function removeUser(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $todo = Todo::findOrFail($id);
        $user = User::findOrFail($request->user_id);
        
        $user->removeTodo($todo);
        
        return response()->json([
            'message' => 'User association removed successfully'
        ]);
    }
}
