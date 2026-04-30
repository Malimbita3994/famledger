<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    /**
     * Whether this asset belongs on the maintenance log (repairs, inspections for buildings and vehicles).
     * Land parcels, farm/agricultural land, and financial instruments are excluded.
     */
    public function isEligibleForRoutineMaintenance(): bool
    {
        $this->loadMissing(['category', 'subcategory']);

        if ($this->category === null) {
            return true;
        }

        $catSlug = strtolower((string) $this->category->slug);

        if ($catSlug === 'financial_investments') {
            return false;
        }

        if ($catSlug === 'agricultural_assets') {
            return false;
        }

        $cName = mb_strtolower(trim((string) $this->category->name));
        $sName = mb_strtolower(trim((string) ($this->subcategory->name ?? '')));
        $sSlug = mb_strtolower(trim((string) ($this->subcategory->slug ?? '')));

        if ($cName === 'land' || $sName === 'land' || $sSlug === 'land') {
            return false;
        }

        $blob = $cName.' '.$sName;

        if (preg_match('/\b(bare\s+land|vacant\s+land|agricultural\s+land|farmland|farm\s+land|pasture|woodland|orchard|undeveloped\s+land)\b/u', $blob)) {
            return false;
        }

        // Subcategory like "Farm" (land use), not "Farmhouse" / buildings
        if ($sName !== '' && preg_match('/\bfarm\b/u', $sName) && ! preg_match('/\b(farmhouse|farm\s+house|farm\s+building|barn|silo|structure)\b/u', $sName)) {
            return false;
        }

        return true;
    }

    /**
     * Short label for category + subcategory (for UI lists).
     */
    public function categoryLine(): string
    {
        $this->loadMissing(['category', 'subcategory']);
        $parts = array_filter([
            $this->category?->name,
            $this->subcategory?->name,
        ]);

        return $parts !== [] ? implode(' / ', $parts) : '—';
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

    /**
     * Label/value rows for the read-only property detail modal (assets list & show).
     *
     * @return array<int, array{l: string, v: string}>
     */
    public function detailModalRows(Family $family): array
    {
        $ccy = $this->currency_code ?: $family->currency_code;

        $categoryParts = array_filter([
            $this->category?->name,
            $this->subcategory?->name,
        ]);
        $categoryLine = $categoryParts !== [] ? implode(' / ', $categoryParts) : '—';

        $ownership = $this->ownership_type
            ? ucfirst(str_replace('_', ' ', (string) $this->ownership_type))
            : '—';

        $acqMethod = $this->acquisition_method
            ? ucfirst(str_replace('_', ' ', (string) $this->acquisition_method))
            : '—';

        $purchase = $this->purchase_price !== null
            ? number_format((float) $this->purchase_price, 2).' '.$ccy
            : '—';

        $estimated = $this->current_estimated_value !== null
            ? number_format((float) $this->current_estimated_value, 2).' '.$ccy
            : '—';

        $gps = ($this->gps_lat !== null && $this->gps_lng !== null)
            ? $this->gps_lat.', '.$this->gps_lng
            : '—';

        $rows = [
            ['l' => __('Code'), 'v' => $this->property_code ?: '—'],
            ['l' => __('Name'), 'v' => $this->name ?: '—'],
            ['l' => __('Status'), 'v' => $this->status ? ucfirst((string) $this->status) : '—'],
            ['l' => __('Category'), 'v' => $categoryLine],
            ['l' => __('Ownership type'), 'v' => $ownership],
            ['l' => __('Currency'), 'v' => $ccy],
            ['l' => __('Acquisition date'), 'v' => $this->acquisition_date ? $this->acquisition_date->format('Y-m-d') : '—'],
            ['l' => __('Acquisition method'), 'v' => $acqMethod],
            ['l' => __('Purchase price'), 'v' => $purchase],
            ['l' => __('Current estimated value'), 'v' => $estimated],
        ];

        if ($this->valuation_date) {
            $rows[] = ['l' => __('Valuation date'), 'v' => $this->valuation_date->format('Y-m-d')];
        }

        $rows[] = ['l' => __('Country'), 'v' => $this->country ?: ($family->country ?: '—')];
        $rows[] = ['l' => __('Region / City'), 'v' => $this->region_city ?: '—'];
        $rows[] = ['l' => __('Address'), 'v' => $this->address ?: '—', 'full' => true];
        $rows[] = ['l' => __('GPS'), 'v' => $gps];
        $rows[] = ['l' => __('Title number / registration'), 'v' => $this->title_number ?: '—'];
        $rows[] = ['l' => __('Notes'), 'v' => $this->notes !== null && $this->notes !== '' ? (string) $this->notes : '—', 'full' => true];

        return $rows;
    }
}
