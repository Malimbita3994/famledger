<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class IncomeCategory extends Model
{
    protected $fillable = ['family_id', 'name', 'parent_id', 'sort_order'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
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

    /**
     * Top-level income groups for the create form (seeded with sort_order 1–7).
     */
    public static function hierarchicalDefaultsForForms(): Collection
    {
        return static::query()
            ->whereNull('family_id')
            ->whereNull('parent_id')
            ->where('sort_order', '>', 0)
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->orderBy('name')])
            ->get();
    }

    public function getDisplayLabelAttribute(): string
    {
        if ($this->relationLoaded('parent') && $this->parent) {
            return $this->parent->name.' › '.$this->name;
        }

        return $this->name;
    }
}
