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
            // Core legacy categories
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

            // Structured income categories
            // Wages
            'Wages - Paycheck',
            'Wages - Tips',
            'Wages - Bonus',
            'Wages - Commission',
            'Wages - Other',

            // Other income
            'Other income - Transfer from savings',
            'Other income - Interest income',
            'Other income - Dividends',
            'Other income - Gifts',
            'Other income - Refunds',
            'Other income - Other',
        ];

        foreach ($defaults as $name) {
            IncomeCategory::firstOrCreate(
                ['name' => $name, 'family_id' => null],
                ['name' => $name]
            );
        }
    }
}
