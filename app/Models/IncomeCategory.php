<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeCategory extends Model
{
    protected $fillable = ['family_id', 'name'];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class, 'category_id');
    }

    /**
     * System default categories (family_id = null).
     */
    public static function defaults()
    {
        return static::whereNull('family_id')->orderBy('name')->get();
    }
}
