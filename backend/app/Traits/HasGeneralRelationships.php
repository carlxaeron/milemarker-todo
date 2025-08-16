<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use App\Models\Todo;

trait HasGeneralRelationships
{
    /**
     * Get all general relationships for this model.
     */
    public function generalRelationships(): MorphMany
    {
        return $this->morphMany(\App\Models\GeneralMap::class, 'mappable');
    }

    /**
     * Get relationships of a specific type.
     */
    public function getRelationships(string $relationshipType, ?string $relationshipKey = null)
    {
        $query = DB::table('general_maps')
            ->where('mappable_type', get_class($this))
            ->where('mappable_id', $this->id)
            ->where('relationship_type', $relationshipType)
            ->where('is_active', true);

        if ($relationshipKey) {
            $query->where('relationship_key', $relationshipKey);
        }

        return $query->orderBy('sort_order')->get();
    }

    /**
     * Create a general relationship.
     */
    public function createRelationship(
        string $relatedType,
        int $relatedId,
        string $relationshipType = 'general',
        ?string $relationshipKey = null,
        ?array $metadata = null,
        int $sortOrder = 0
    ): \App\Models\GeneralMap {
        return \App\Models\GeneralMap::create([
            'mappable_type' => get_class($this),
            'mappable_id' => $this->id,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'relationship_type' => $relationshipType,
            'relationship_key' => $relationshipKey,
            'metadata' => $metadata,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Remove a general relationship.
     */
    public function removeRelationship(
        string $relatedType,
        int $relatedId,
        string $relationshipType = 'general',
        ?string $relationshipKey = null
    ): bool {
        return DB::table('general_maps')
            ->where('mappable_type', get_class($this))
            ->where('mappable_id', $this->id)
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->where('relationship_type', $relationshipType)
            ->when($relationshipKey, fn($query) => $query->where('relationship_key', $relationshipKey))
            ->delete() > 0;
    }

    /**
     * Get todos with additional metadata using general relationships.
     */
    public function getTodosWithMetadata(?string $relationshipKey = null)
    {
        $relationships = $this->getRelationships('todo_metadata', $relationshipKey);
        
        $todoIds = $relationships->pluck('related_id')->toArray();
        
        if (empty($todoIds)) {
            return collect();
        }

        $todos = Todo::whereIn('id', $todoIds)->get();
        
        // Attach metadata to todos
        foreach ($todos as $todo) {
            $relationship = $relationships->firstWhere('related_id', $todo->id);
            if ($relationship) {
                $todo->relationship_metadata = $relationship->metadata;
                $todo->relationship_sort_order = $relationship->sort_order;
            }
        }

        return $todos->sortBy('relationship_sort_order');
    }
}
