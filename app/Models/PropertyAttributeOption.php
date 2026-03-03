<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAttributeOption extends Model
{
    protected $fillable = [
        'attribute_id',
        'value',
        'label',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'int',
        ];
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(PropertyAttribute::class, 'attribute_id');
    }
}

