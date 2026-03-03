<?php

namespace Database\Seeders;

use App\Models\IncomeCategory;
use Illuminate\Database\Seeder;

class IncomeCategorySeeder extends Seeder
{
    /**
     * System default income categories (family_id = null).
     */
    public function run(): void
    {
        $defaults = [
            'Salary',
            'Business',
            'Gift',
            'Refund',
            'Interest',
            'Allowance',
            'Sale',
            'Contribution',
            'Reconciliation Adjustment',
            'Other',
        ];

        foreach ($defaults as $name) {
            IncomeCategory::firstOrCreate(
                ['name' => $name, 'family_id' => null],
                ['name' => $name]
            );
        }
    }
}
