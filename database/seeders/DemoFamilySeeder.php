<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FamilyRole;
use App\Models\Goal;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rich sample family for staging / customer demos. Idempotent: clears prior demo
 * transactions for the configured demo family, then recreates wallets, activity,
 * and goals.
 *
 * Run: php artisan db:seed --class=DemoFamilySeeder
 */
class DemoFamilySeeder extends Seeder
{
    public function run(): void
    {
        $cfg = config('famledger.demo_seed', []);
        $email = (string) ($cfg['user_email'] ?? 'demo@famledger.local');
        $plainPassword = (string) ($cfg['user_password'] ?? 'password');
        $familyName = (string) ($cfg['family_name'] ?? 'Rivera family (demo)');

        $ownerRole = FamilyRole::query()->where('name', 'Owner')->firstOrFail();

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Alex Rivera',
                'password' => $plainPassword,
                'email_verified_at' => now(),
                'status' => User::STATUS_ACTIVE,
            ]
        );

        $currency = strtoupper((string) config('currencies.default', 'TZS'));
        $now = Carbon::now();

        DB::transaction(function () use ($user, $ownerRole, $familyName, $currency, $now) {
            $family = Family::query()->where('name', $familyName)->first();

            if ($family) {
                $this->purgeDemoFamilyData($family);
            } else {
                $family = Family::create([
                    'name' => $familyName,
                    'description' => 'Sample household for product demos (staging).',
                    'currency_code' => $currency,
                    'timezone' => 'Africa/Nairobi',
                    'country' => 'KE',
                    'created_by' => $user->id,
                    'status' => 'active',
                ]);

                FamilyMember::create([
                    'family_id' => $family->id,
                    'user_id' => $user->id,
                    'role_id' => $ownerRole->id,
                    'is_primary' => true,
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            }

            // Ensure membership (e.g. family existed from a partial run)
            FamilyMember::query()->firstOrCreate(
                [
                    'family_id' => $family->id,
                    'user_id' => $user->id,
                ],
                [
                    'role_id' => $ownerRole->id,
                    'is_primary' => true,
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );

            $main = $family->ensureDefaultMainWallet($user->id);
            $main->update([
                'name' => 'Main account',
                'initial_balance' => 0,
            ]);

            $savings = Wallet::query()->firstOrCreate(
                [
                    'family_id' => $family->id,
                    'name' => 'Emergency fund',
                ],
                [
                    'type' => 'emergency_fund',
                    'currency_code' => $currency,
                    'description' => 'Rainy-day savings',
                    'initial_balance' => 0,
                    'is_primary' => false,
                    'is_shared' => true,
                    'status' => 'active',
                    'created_by' => $user->id,
                ]
            );

            $salaryCategoryId = $this->incomeCategoryId('Salary', 'Employment');
            $freelanceCategoryId = $this->incomeCategoryId('Freelance', 'Employment');

            $incomeRows = [
                [
                    'amount' => 2_800_000,
                    'received_date' => $now->copy()->startOfMonth()->addDays(2),
                    'notes' => 'Monthly salary',
                    'category_id' => $salaryCategoryId,
                    'source' => 'Northwind Logistics',
                    'source_entity_type' => Income::SOURCE_ENTITY_EMPLOYER,
                ],
                [
                    'amount' => 450_000,
                    'received_date' => $now->copy()->startOfMonth()->addDays(14),
                    'notes' => 'UI contract milestone',
                    'category_id' => $freelanceCategoryId,
                    'source' => 'Design client',
                    'source_entity_type' => Income::SOURCE_ENTITY_CLIENT,
                ],
            ];

            foreach ($incomeRows as $row) {
                Income::create([
                    'family_id' => $family->id,
                    'wallet_id' => $main->id,
                    'category_id' => $row['category_id'],
                    'amount' => $row['amount'],
                    'currency_code' => $currency,
                    'source' => $row['source'],
                    'source_entity_type' => $row['source_entity_type'],
                    'received_date' => $row['received_date'],
                    'notes' => $row['notes'],
                    'received_by' => $user->id,
                    'created_by' => $user->id,
                    'is_recurring' => false,
                    'is_taxable' => true,
                ]);
            }

            Transfer::create([
                'family_id' => $family->id,
                'from_wallet_id' => $main->id,
                'to_wallet_id' => $savings->id,
                'amount' => 400_000,
                'currency_code' => $currency,
                'transfer_date' => $now->copy()->startOfMonth()->addDays(5),
                'description' => 'Monthly emergency fund contribution',
                'reference' => 'DEMO-XFER-1',
                'created_by' => $user->id,
            ]);

            $expenseSpecs = [
                ['name' => 'Groceries', 'amount' => 320_000, 'day' => 4, 'merchant' => 'GreenMarket Co-op', 'description' => 'Monthly groceries'],
                ['name' => 'Rent', 'amount' => 850_000, 'day' => 1, 'merchant' => 'Landlord transfer', 'description' => 'Rent'],
                ['name' => 'Utilities', 'amount' => 95_000, 'day' => 6, 'merchant' => 'Power & water', 'description' => 'Utilities'],
                ['name' => 'Fuel', 'amount' => 180_000, 'day' => 9, 'merchant' => 'CityFuel', 'description' => 'Fuel'],
                ['name' => 'School fees', 'amount' => 240_000, 'day' => 8, 'merchant' => 'Lakeside Academy', 'description' => 'Tuition installment'],
                ['name' => 'Dining out', 'amount' => 125_000, 'day' => 12, 'merchant' => 'Various', 'description' => 'Weekend meals'],
                ['name' => 'Medical', 'amount' => 60_000, 'day' => 18, 'merchant' => 'Family clinic', 'description' => 'Check-up'],
            ];

            foreach ($expenseSpecs as $spec) {
                $catId = ExpenseCategory::query()
                    ->whereNull('family_id')
                    ->where('name', $spec['name'])
                    ->value('id');

                Expense::create([
                    'family_id' => $family->id,
                    'wallet_id' => $main->id,
                    'category_id' => $catId,
                    'amount' => $spec['amount'],
                    'currency_code' => $currency,
                    'description' => $spec['description'],
                    'expense_date' => $now->copy()->startOfMonth()->addDays($spec['day'] - 1),
                    'paid_by' => $user->id,
                    'merchant' => $spec['merchant'],
                    'payment_method' => 'mobile_money',
                    'created_by' => $user->id,
                    'is_recurring' => false,
                ]);
            }

            Goal::query()->where('family_id', $family->id)->delete();

            Goal::create([
                'family_id' => $family->id,
                'title' => 'Family vacation fund',
                'description' => 'Save for a week at the coast next summer.',
                'status' => 'active',
                'progress' => 35,
                'target_date' => $now->copy()->addMonths(8)->startOfMonth(),
                'category' => 'Travel',
            ]);

            Goal::create([
                'family_id' => $family->id,
                'title' => 'Home maintenance reserve',
                'description' => 'Roof inspection and small repairs.',
                'status' => 'active',
                'progress' => 60,
                'target_date' => $now->copy()->addMonths(3)->endOfMonth(),
                'category' => 'Housing',
            ]);
        });

        if ($this->command) {
            $this->command->info('Demo family seeded: '.$familyName.' (login: '.$email.' / '.$plainPassword.')');
        }
    }

    private function purgeDemoFamilyData(Family $family): void
    {
        Transfer::query()->where('family_id', $family->id)->delete();
        Income::query()->where('family_id', $family->id)->delete();
        Expense::query()->where('family_id', $family->id)->delete();
        Goal::query()->where('family_id', $family->id)->delete();

        Wallet::query()
            ->where('family_id', $family->id)
            ->where('is_primary', false)
            ->delete();
    }

    private function incomeCategoryId(string $childName, string $parentName): ?int
    {
        $parent = IncomeCategory::query()
            ->whereNull('family_id')
            ->whereNull('parent_id')
            ->where('name', $parentName)
            ->first();

        if ($parent) {
            $id = IncomeCategory::query()
                ->whereNull('family_id')
                ->where('parent_id', $parent->id)
                ->where('name', $childName)
                ->value('id');
            if ($id) {
                return (int) $id;
            }
        }

        return IncomeCategory::query()
            ->whereNull('family_id')
            ->where('name', $childName)
            ->value('id');
    }
}
