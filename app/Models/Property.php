<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_id',
        'property_code',
        'name',
        'category_id',
        'subcategory_id',
        'ownership_type',
        'owner_family_member_id',
        'acquisition_date',
        'acquisition_method',
        'purchase_price',
        'current_estimated_value',
        'valuation_date',
        'currency_code',
        'depreciation_method',
        'useful_life_years',
        'status',
        'insurance_status',
        'legal_status',
        'country',
        'region_city',
        'address',
        'gps_lat',
        'gps_lng',
        'title_number',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'valuation_date' => 'date',
            'purchase_price' => 'decimal:2',
            'current_estimated_value' => 'decimal:2',
            'gps_lat' => 'float',
            'gps_lng' => 'float',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PropertyCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(PropertyCategory::class, 'subcategory_id');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(PropertyAttributeValue::class, 'property_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(PropertyMaintenance::class, 'property_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PropertyDocument::class, 'property_id');
    }

    public function valuations(): HasMany
    {
        return $this->hasMany(PropertyValuation::class, 'property_id');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(PropertyDepreciation::class, 'property_id');
    }
}

