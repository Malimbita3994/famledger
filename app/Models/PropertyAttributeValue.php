<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAttributeValue extends Model
{
    protected $fillable = [
        'property_id',
        'attribute_id',
        'value',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(PropertyAttribute::class, 'attribute_id');
    }
}

