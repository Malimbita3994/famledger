<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FamLedger Financial Year
    |--------------------------------------------------------------------------
    |
    | The financial year is used for reports, budgets and period-based views.
    | Default: calendar year (January 1 – December 31).
    |
    | To use a different fiscal year (e.g. July 1 – June 30), set in .env:
    |   FAMLEDGER_FY_START_MONTH=7
    |   FAMLEDGER_FY_START_DAY=1
    |   FAMLEDGER_FY_END_MONTH=6
    |   FAMLEDGER_FY_END_DAY=30
    |
    */

    'financial_year' => [
        'start_month' => (int) env('FAMLEDGER_FY_START_MONTH', 1),
        'start_day' => (int) env('FAMLEDGER_FY_START_DAY', 1),
        'end_month' => (int) env('FAMLEDGER_FY_END_MONTH', 12),
        'end_day' => (int) env('FAMLEDGER_FY_END_DAY', 31),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Alerts
    |--------------------------------------------------------------------------
    | Main wallet "low balance" threshold (dashboard alert when below this).
    */

    'low_balance_threshold' => (float) env('FAMLEDGER_LOW_BALANCE_THRESHOLD', 100000),

    /*
    |--------------------------------------------------------------------------
    | Wallet balance floor
    |--------------------------------------------------------------------------
    | When false (default), expenses cannot reduce a wallet's derived balance
    | below zero. Transfers out were already limited the same way.
    | Set true only if you need overdraft-style tracking (not recommended).
    */

    'allow_negative_wallet_balance' => (bool) env('FAMLEDGER_ALLOW_NEGATIVE_WALLET_BALANCE', false),

    /*
    |--------------------------------------------------------------------------
    | Default password for newly invited family members
    |--------------------------------------------------------------------------
    |
    | When adding a member by email and that email has no user account yet,
    | a user is created with this plain-text password (then hashed). The same
    | value is emailed in MemberCredentialsMail (web). Override in .env for
    | each environment; use a strong secret in production.
    |
    */

    'default_new_member_password' => env('FAMLEDGER_DEFAULT_NEW_MEMBER_PASSWORD', 'FamLedgerMember123!'),

    /*
    |--------------------------------------------------------------------------
    | Staging / demo dataset (DemoFamilySeeder)
    |--------------------------------------------------------------------------
    |
    | When FAMLEDGER_DEMO_SEED=true, `php artisan db:seed` also runs DemoFamilySeeder.
    | You can always run the seeder alone: `php artisan db:seed --class=DemoFamilySeeder`
    |
    */

    'demo_seed' => [
        'enabled' => (bool) env('FAMLEDGER_DEMO_SEED', false),
        'user_email' => env('FAMLEDGER_DEMO_USER_EMAIL', 'demo@famledger.local'),
        'user_password' => env('FAMLEDGER_DEMO_USER_PASSWORD', 'password'),
        'family_name' => env('FAMLEDGER_DEMO_FAMILY_NAME', 'Rivera family (demo)'),
    ],

];
