<?php

use App\Models\Family;
use App\Models\FamilyRole;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\ExpenseCategorySeeder;
use Database\Seeders\FamilyRoleSeeder;
use Database\Seeders\IncomeCategorySeeder;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Seed roles + default income/expense categories (required for transaction forms).
 */
function seedFinanceCatalog(): void
{
    (new FamilyRoleSeeder)->run();
    (new IncomeCategorySeeder)->run();
    (new ExpenseCategorySeeder)->run();
}

/**
 * @return array{user: User, family: Family, wallet: Wallet}
 */
function createFamilyWithMember(?User $user = null): array
{
    seedFinanceCatalog();

    $user = $user ?? User::factory()->create();

    $family = Family::create([
        'name' => 'Test Family',
        'currency_code' => 'USD',
        'created_by' => $user->id,
        'status' => 'active',
    ]);

    $roleId = FamilyRole::where('name', 'Owner')->firstOrFail()->id;

    $family->members()->attach($user->id, [
        'role_id' => $roleId,
        'status' => 'active',
        'is_primary' => true,
    ]);

    $wallet = Wallet::create([
        'family_id' => $family->id,
        'name' => 'Main',
        'type' => 'cash',
        'currency_code' => 'USD',
        'initial_balance' => 0,
        'is_shared' => true,
        'status' => 'active',
        'created_by' => $user->id,
        'is_primary' => true,
    ]);

    return compact('user', 'family', 'wallet');
}
