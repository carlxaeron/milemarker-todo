<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralMap extends Model
{
    protected $table = 'general_maps';

    protected $fillable = [
        'mappable_type',
        'mappable_id',
        'related_type',
        'related_id',
        'relationship_type',
        'relationship_key',
        'metadata',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the parent mappable model.
     */
    public function mappable()
    {
        return $this->morphTo();
    }

    /**
     * Get the related model.
     */
    public function related()
    {
        return $this->morphTo('related');
    }
}
