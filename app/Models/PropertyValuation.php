<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyValuation extends Model
{
    protected $fillable = [
        'property_id',
        'valuation_date',
        'estimated_value',
        'valuator',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'valuation_date' => 'date',
            'estimated_value' => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

