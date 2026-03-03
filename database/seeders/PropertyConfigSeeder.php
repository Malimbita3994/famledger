<?php

namespace Database\Seeders;

use App\Models\PropertyAttribute;
use App\Models\PropertyAttributeOption;
use App\Models\PropertyCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Core categories
        $categories = [
            'vehicles' => 'Vehicles',
            'real_estate' => 'Real Estate',
            'agricultural_assets' => 'Agricultural Assets',
            'financial_investments' => 'Financial Investments',
        ];

        $categoryModels = [];
        foreach ($categories as $slug => $name) {
            $categoryModels[$slug] = PropertyCategory::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true]
            );
        }

        // Helper to create attributes quickly
        $makeAttr = function (PropertyCategory $cat, string $name, string $dataType, array $flags = [], int $sort = 0) {
            $slug = Str::slug($name, '_');

            return PropertyAttribute::firstOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $cat->id,
                    'name' => $name,
                    'data_type' => $dataType,
                    'is_required' => $flags['required'] ?? false,
                    'is_searchable' => $flags['searchable'] ?? false,
                    'is_reportable' => $flags['reportable'] ?? false,
                    'sort_order' => $sort,
                ]
            );
        };

        // Vehicles
        $veh = $categoryModels['vehicles'];
        $makeAttr($veh, 'Registration Number', 'text', ['required' => true, 'searchable' => true], 10);
        $makeAttr($veh, 'Engine Number', 'text', [], 20);
        $makeAttr($veh, 'Chassis Number', 'text', [], 30);
        $makeAttr($veh, 'Insurance Expiry Date', 'date', [], 40);
        $makeAttr($veh, 'Road License Expiry', 'date', [], 50);
        $fuel = $makeAttr($veh, 'Fuel Type', 'dropdown', [], 60);
        $makeAttr($veh, 'Mileage', 'number', ['reportable' => true], 70);
        $makeAttr($veh, 'Service Interval (KM)', 'number', [], 80);
        $trans = $makeAttr($veh, 'Transmission Type', 'dropdown', [], 90);

        if ($fuel->wasRecentlyCreated) {
            foreach (['Petrol', 'Diesel', 'Hybrid', 'Electric', 'Other'] as $i => $label) {
                PropertyAttributeOption::firstOrCreate(
                    ['attribute_id' => $fuel->id, 'value' => Str::slug($label, '_')],
                    ['label' => $label, 'sort_order' => $i + 1]
                );
            }
        }

        if ($trans->wasRecentlyCreated) {
            foreach (['Manual', 'Automatic', 'Other'] as $i => $label) {
                PropertyAttributeOption::firstOrCreate(
                    ['attribute_id' => $trans->id, 'value' => Str::slug($label, '_')],
                    ['label' => $label, 'sort_order' => $i + 1]
                );
            }
        }

        // Real Estate
        $re = $categoryModels['real_estate'];
        $makeAttr($re, 'Title Deed Number', 'text', ['required' => true, 'searchable' => true], 10);
        $makeAttr($re, 'Plot Number', 'text', [], 20);
        $size = $makeAttr($re, 'Size', 'number', ['reportable' => true], 30);
        $sizeUnit = $makeAttr($re, 'Size Unit', 'dropdown', [], 35);
        $btype = $makeAttr($re, 'Building Type', 'dropdown', [], 40);
        $makeAttr($re, 'Rental Income (Monthly)', 'currency', ['reportable' => true], 50);
        $makeAttr($re, 'Property Tax Amount', 'currency', ['reportable' => true], 60);
        $makeAttr($re, 'Number of Units', 'number', ['reportable' => true], 70);
        $makeAttr($re, 'Construction Year', 'number', [], 80);
        $occ = $makeAttr($re, 'Occupancy Status', 'dropdown', [], 90);

        if ($sizeUnit->wasRecentlyCreated) {
            foreach (['sqm', 'acre'] as $i => $value) {
                PropertyAttributeOption::firstOrCreate(
                    ['attribute_id' => $sizeUnit->id, 'value' => $value],
                    ['label' => strtoupper($value), 'sort_order' => $i + 1]
                );
            }
        }

        if ($btype->wasRecentlyCreated) {
            foreach (['Apartment', 'House', 'Commercial', 'Mixed Use', 'Other'] as $i => $label) {
                PropertyAttributeOption::firstOrCreate(
                    ['attribute_id' => $btype->id, 'value' => Str::slug($label, '_')],
                    ['label' => $label, 'sort_order' => $i + 1]
                );
            }
        }

        if ($occ->wasRecentlyCreated) {
            foreach (['Owner occupied', 'Rented', 'Vacant'] as $i => $label) {
                PropertyAttributeOption::firstOrCreate(
                    ['attribute_id' => $occ->id, 'value' => Str::slug($label, '_')],
                    ['label' => $label, 'sort_order' => $i + 1]
                );
            }
        }

        // Agricultural Assets
        $ag = $categoryModels['agricultural_assets'];
        $makeAttr($ag, 'Livestock Type', 'text', [], 10);
        $makeAttr($ag, 'Quantity', 'number', ['reportable' => true], 20);
        $makeAttr($ag, 'Breed', 'text', [], 30);
        $makeAttr($ag, 'Production Capacity', 'text', [], 40);
        $makeAttr($ag, 'Farm Size', 'number', ['reportable' => true], 50);
        $makeAttr($ag, 'Irrigation Type', 'text', [], 60);
        $makeAttr($ag, 'Annual Yield Estimate', 'text', [], 70);

        // Financial Investments
        $fi = $categoryModels['financial_investments'];
        $makeAttr($fi, 'Investment Institution', 'text', [], 10);
        $makeAttr($fi, 'Account Number', 'text', ['searchable' => true], 20);
        $makeAttr($fi, 'Interest Rate', 'number', ['reportable' => true], 30);
        $makeAttr($fi, 'Maturity Date', 'date', [], 40);
        $makeAttr($fi, 'Dividend Frequency', 'dropdown', [], 50);
        $makeAttr($fi, 'Risk Level', 'dropdown', [], 60);
        $makeAttr($fi, 'Lock-in Period', 'text', [], 70);

        $freq = PropertyAttribute::where('slug', 'dividend_frequency')->first();
        if ($freq && $freq->wasRecentlyCreated) {
            foreach (['Monthly', 'Quarterly', 'Annually', 'Ad-hoc'] as $i => $label) {
                PropertyAttributeOption::firstOrCreate(
                    ['attribute_id' => $freq->id, 'value' => Str::slug($label, '_')],
                    ['label' => $label, 'sort_order' => $i + 1]
                );
            }
        }

        $risk = PropertyAttribute::where('slug', 'risk_level')->first();
        if ($risk && $risk->wasRecentlyCreated) {
            foreach (['Low', 'Medium', 'High'] as $i => $label) {
                PropertyAttributeOption::firstOrCreate(
                    ['attribute_id' => $risk->id, 'value' => Str::slug($label, '_')],
                    ['label' => $label, 'sort_order' => $i + 1]
                );
            }
        }
    }
}

