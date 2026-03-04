<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDepreciation extends Model
{
    protected $fillable = [
        'property_id',
        'method',
        'year',
        'depreciation_amount',
        'book_value',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'depreciation_amount' => 'decimal:2',
            'book_value' => 'decimal:2',
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

