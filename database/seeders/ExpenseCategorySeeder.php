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
            // Core legacy categories
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

            // Structured family budget style categories
            // Children
            'Children - Activities',
            'Children - Allowance',
            'Children - Medical',
            'Children - Childcare',
            'Children - Clothing',
            'Children - School',
            'Children - Toys',
            'Children - Other',

            // Debt
            'Debt - Credit cards',
            'Debt - Student loans',
            'Debt - Other loans',
            'Debt - Taxes (federal)',
            'Debt - Taxes (state)',
            'Debt - Other',

            // Education
            'Education - Tuition',
            'Education - Books',
            'Education - Music lessons',
            'Education - Other',

            // Entertainment
            'Entertainment - Books',
            'Entertainment - Concerts/shows',
            'Entertainment - Games',
            'Entertainment - Hobbies',
            'Entertainment - Movies',
            'Entertainment - Music',
            'Entertainment - Outdoor activities',
            'Entertainment - Photography',
            'Entertainment - Sports',
            'Entertainment - Theater/plays',
            'Entertainment - TV',
            'Entertainment - Other',

            // Everyday
            'Everyday - Groceries',
            'Everyday - Restaurants',
            'Everyday - Personal supplies',
            'Everyday - Clothes',
            'Everyday - Laundry/dry cleaning',
            'Everyday - Hair/beauty',
            'Everyday - Subscriptions',
            'Everyday - Other',

            // Gifts
            'Gifts - Gifts',
            'Gifts - Donations (charity)',
            'Gifts - Other',

            // Health / medical
            'Health/medical - Doctors/dental/vision',
            'Health/medical - Specialty care',
            'Health/medical - Pharmacy',
            'Health/medical - Emergency',
            'Health/medical - Other',

            // Home
            'Home - Rent/mortgage',
            'Home - Property taxes',
            'Home - Furnishings',
            'Home - Lawn/garden',
            'Home - Supplies',
            'Home - Maintenance',
            'Home - Improvements',
            'Home - Moving',
            'Home - Other',

            // Insurance
            'Insurance - Car',
            'Insurance - Health',
            'Insurance - Home',
            'Insurance - Life',
            'Insurance - Other',

            // Pets
            'Pets - Food',
            'Pets - Vet/medical',
            'Pets - Toys',
            'Pets - Supplies',
            'Pets - Other',

            // Technology
            'Technology - Domains & hosting',
            'Technology - Online services',
            'Technology - Hardware',
            'Technology - Software',
            'Technology - Other',

            // Transportation
            'Transportation - Fuel',
            'Transportation - Car payments',
            'Transportation - Repairs',
            'Transportation - Registration/license',
            'Transportation - Supplies',
            'Transportation - Public transit',
            'Transportation - Other',

            // Travel
            'Travel - Airfare',
            'Travel - Hotels',
            'Travel - Food',
            'Travel - Transportation',
            'Travel - Entertainment',
            'Travel - Other',

            // Utilities
            'Utilities - Phone',
            'Utilities - TV',
            'Utilities - Internet',
            'Utilities - Electricity',
            'Utilities - Heat/gas',
            'Utilities - Water',
            'Utilities - Trash',
            'Utilities - Other',

            // Other / custom
            'Other - Category 1',
            'Other - Category 2',
        ];

        foreach ($defaults as $name) {
            ExpenseCategory::firstOrCreate(
                ['name' => $name, 'family_id' => null],
                ['name' => $name]
            );
        }
    }
}
