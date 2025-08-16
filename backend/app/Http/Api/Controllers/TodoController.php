<?php

namespace App\Http\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $todos = Todo::with(['user', 'user.generalRelationships'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Enhance todos with relationship metadata
        $todos->each(function ($todo) {
            if ($todo->user) {
                $todo->user_relationships = $todo->user->getRelationships('todo_metadata', null);
                $todo->is_favorite = $todo->user->getRelationships('favorite')
                    ->where('related_id', $todo->id)
                    ->count() > 0;
                $todo->is_shared = $todo->user->getRelationships('shared')
                    ->where('related_id', $todo->id)
                    ->count() > 0;
            }
        });
        
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
            'user_id' => 'nullable|exists:users,id'
        ]);

        $todo = Todo::create($request->all());
        
        // Load user and relationships for response
        $todo->load(['user', 'user.generalRelationships']);
        
        return response()->json($todo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);
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
            'completed' => 'boolean'
        ]);

        $todo = Todo::findOrFail($id);
        $todo->update($request->all());
        
        return response()->json($todo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);
        $todo->delete();
        
        return response()->json(null, 204);
    }
}
