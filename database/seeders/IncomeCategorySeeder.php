<?php

namespace Database\Seeders;

use App\Models\IncomeCategory;
use Illuminate\Database\Seeder;

class IncomeCategorySeeder extends Seeder
{
    /**
     * System default income categories (family_id = null).
     * Top-level groups use sort_order 1–7 so the income form can list them;
     * legacy flat rows keep sort_order 0.
     */
    public function run(): void
    {
        $tree = [
            ['sort' => 1, 'name' => 'Employment', 'children' => [
                'Salary', 'Bonus', 'Commission', 'Tips', 'Freelance', 'Overtime', 'Other',
            ]],
            ['sort' => 2, 'name' => 'Business', 'children' => [
                'Sales revenue', 'Service revenue', 'Consulting', 'Contract work', 'Other',
            ]],
            ['sort' => 3, 'name' => 'Investment', 'children' => [
                'Dividends', 'Interest', 'Capital gains', 'Distributions', 'Other',
            ]],
            ['sort' => 4, 'name' => 'Passive', 'children' => [
                'Rental income', 'Royalties', 'Licensing', 'Affiliate / passive', 'Other',
            ]],
            ['sort' => 5, 'name' => 'Other', 'children' => [
                'Gift', 'Refund', 'Reimbursement', 'Allowance', 'Reconciliation adjustment', 'Miscellaneous',
            ]],
            ['sort' => 6, 'name' => 'Project', 'children' => [
                'Milestone payment', 'Grant', 'Sponsorship', 'Reimbursement', 'Other',
            ]],
            ['sort' => 7, 'name' => 'Government', 'children' => [
                'Benefits', 'Tax credit', 'Pension', 'Subsidy', 'Stimulus / relief', 'Other',
            ]],
        ];

        foreach ($tree as $node) {
            $parent = IncomeCategory::query()->updateOrCreate(
                [
                    'family_id' => null,
                    'parent_id' => null,
                    'name' => $node['name'],
                ],
                [
                    'sort_order' => $node['sort'],
                ]
            );

            foreach ($node['children'] as $childName) {
                IncomeCategory::query()->firstOrCreate(
                    [
                        'family_id' => null,
                        'parent_id' => $parent->id,
                        'name' => $childName,
                    ],
                    [
                        'name' => $childName,
                        'sort_order' => 0,
                    ]
                );
            }
        }

        // Legacy flat names (unchanged) — kept for older data / imports; sort_order stays 0.
        $legacyFlat = [
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
            'Wages - Paycheck',
            'Wages - Tips',
            'Wages - Bonus',
            'Wages - Commission',
            'Wages - Other',
            'Other income - Transfer from savings',
            'Other income - Interest income',
            'Other income - Dividends',
            'Other income - Gifts',
            'Other income - Refunds',
            'Other income - Other',
        ];

        foreach ($legacyFlat as $name) {
            IncomeCategory::query()->firstOrCreate(
                [
                    'name' => $name,
                    'family_id' => null,
                    'parent_id' => null,
                ],
                [
                    'name' => $name,
                    'sort_order' => 0,
                ]
            );
        }
    }
}
