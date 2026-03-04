<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyMaintenance extends Model
{
    protected $fillable = [
        'property_id',
        'service_date',
        'cost',
        'service_provider',
        'description',
        'next_due_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'next_due_date' => 'date',
            'cost' => 'decimal:2',
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

