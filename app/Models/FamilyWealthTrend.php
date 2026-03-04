<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyWealthTrend extends Model
{
    protected $fillable = [
        'family_id',
        'wallet_total',
        'property_total',
        'project_total',
        'liability_total',
        'net_wealth',
        'snapshot_date',
    ];

    protected $casts = [
        'wallet_total' => 'decimal:2',
        'property_total' => 'decimal:2',
        'project_total' => 'decimal:2',
        'liability_total' => 'decimal:2',
        'net_wealth' => 'decimal:2',
        'snapshot_date' => 'date',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
