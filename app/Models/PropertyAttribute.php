<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyAttribute extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'data_type',
        'is_required',
        'is_searchable',
        'is_reportable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'is_reportable' => 'boolean',
            'sort_order' => 'int',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PropertyCategory::class, 'category_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PropertyAttributeOption::class, 'attribute_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(PropertyAttributeValue::class, 'attribute_id');
    }
}

