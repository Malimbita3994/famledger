<?php

use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\User;

test('guest is redirected from family transactions index', function () {
    ['family' => $family] = createFamilyWithMember();

    $this->get(route('families.transactions.index', $family))
        ->assertRedirect(route('login', absolute: false));
});

test('non-member cannot view family transactions', function () {
    ['family' => $family] = createFamilyWithMember();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('families.transactions.index', $family))
        ->assertForbidden();
});

test('member cannot post transaction to another family', function () {
    ['user' => $memberA] = createFamilyWithMember();
    ['family' => $familyB] = createFamilyWithMember();

    $categoryId = IncomeCategory::whereNull('family_id')->firstOrFail()->id;

    $this->actingAs($memberA)
        ->post(route('families.transactions.store', $familyB), [
            'transaction_type' => 'income',
            'amount' => 50,
            'currency_code' => 'USD',
            'category_id' => $categoryId,
            'received_date' => '2026-03-01',
        ])
        ->assertForbidden();
});

test('member can view transactions index', function () {
    ['user' => $user, 'family' => $family] = createFamilyWithMember();

    $this->actingAs($user)
        ->get(route('families.transactions.index', $family))
        ->assertOk();
});

test('member can create income via unified transactions store', function () {
    ['user' => $user, 'family' => $family] = createFamilyWithMember();
    $categoryId = IncomeCategory::whereNull('family_id')->firstOrFail()->id;

    $this->actingAs($user)
        ->post(route('families.transactions.store', $family), [
            'transaction_type' => 'income',
            'amount' => 125.5,
            'currency_code' => 'USD',
            'category_id' => $categoryId,
            'received_date' => '2026-03-10',
            'source' => 'Paycheck',
        ])
        ->assertRedirect(route('families.transactions.index', $family, absolute: false));

    expect($family->fresh()->incomes()->count())->toBe(1)
        ->and((float) $family->incomes()->first()->amount)->toBe(125.5);
});

test('member can create expense via unified transactions store', function () {
    ['user' => $user, 'family' => $family, 'wallet' => $wallet] = createFamilyWithMember();
    $categoryId = ExpenseCategory::whereNull('family_id')->firstOrFail()->id;

    $this->actingAs($user)
        ->post(route('families.transactions.store', $family), [
            'transaction_type' => 'expense',
            'wallet_id' => $wallet->id,
            'amount' => 42,
            'currency_code' => 'USD',
            'category_id' => $categoryId,
            'expense_date' => '2026-03-11',
            'description' => 'Groceries',
        ])
        ->assertRedirect(route('families.transactions.index', $family, absolute: false));

    expect($family->fresh()->expenses()->count())->toBe(1)
        ->and((float) $family->expenses()->first()->amount)->toBe(42.0);
});
