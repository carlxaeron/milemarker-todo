<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carlxaeron\General\Traits\HasGeneralMaps;
use Carlxaeron\General\Models\GeneralMap;

class Todo extends Model
{
    use HasGeneralMaps;

    protected $fillable = [
        'title',
        'description',
        'completed'
    ];

    protected $casts = [
        'completed' => 'boolean'
    ];

    /**
     * Get the users associated with this todo through general maps.
     */
    public function users()
    {
        return $this->generalMaps();
    }

    /**
     * Get the primary user (owner) of this todo.
     */
    public function owner()
    {
        $relationship = GeneralMap::where('related_type', get_class($this))
            ->where('related_id', $this->id)
            ->where('mappable_type', \App\Models\User::class)
            ->where('relationship_type', 'todo_owner')
            ->where('is_active', true)
            ->first();
        
        if ($relationship) {
            return \App\Models\User::find($relationship->mappable_id);
        }
        
        return null;
    }
}
