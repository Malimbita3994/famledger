<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * System default expense categories (family_id = null).
     */
    public function run(): void
    {
        $defaults = [
            'Groceries',
            'Rent',
            'Utilities',
            'Maintenance',
            'Transport',
            'Fuel',
            'School fees',
            'Medical',
            'Entertainment',
            'Dining out',
            'Shopping',
            'Loan repayment',
            'Insurance',
            'Subscriptions',
            'Membership fees',
            'Clothing',
            'Electronics',
            'Furniture',
            'Repairs',
            'Bank fees',
            'Mobile money fees',
            'Taxes',
            'Allowance',
            'Reconciliation Adjustment',
            'Other',
        ];

        foreach ($defaults as $name) {
            ExpenseCategory::firstOrCreate(
                ['name' => $name, 'family_id' => null],
                ['name' => $name]
            );
        }
    }
}
