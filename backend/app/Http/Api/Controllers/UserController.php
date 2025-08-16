<?php

namespace App\Http\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = User::select('id', 'name', 'email', 'created_at')
            ->orderBy('name')
            ->get();
        
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['todos', 'generalRelationships'])->findOrFail($id);
        
        // Get todos with metadata
        $todosWithMetadata = $user->getTodosWithMetadata();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'todos_count' => $user->todos->count(),
            'todos_with_metadata' => $todosWithMetadata,
            'relationships' => $user->generalRelationships
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6'
        ]);

        $user = User::findOrFail($id);
        
        $updateData = $request->only(['name', 'email']);
        if ($request->has('password')) {
            $updateData['password'] = bcrypt($request->password);
        }
        
        $user->update($updateData);
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Get user's todos with relationships.
     */
    public function todos(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $todosWithMetadata = $user->getTodosWithMetadata();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'todos' => $todosWithMetadata
        ]);
    }
}
