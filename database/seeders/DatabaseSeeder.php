<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FamilyRoleSeeder::class,
            IncomeCategorySeeder::class,
            ExpenseCategorySeeder::class,
            AdminRolesAndPermissionsSeeder::class,
            PropertyConfigSeeder::class,
            // Optional: replace all landing / in-app FAQs (destructive): FamLedgerLandingFaqSeeder::class,
        ]);

        // Demo login (idempotent — safe to re-run; resets password to match below)
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => User::STATUS_ACTIVE,
            ]
        );
        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }

        if (config('famledger.demo_seed.enabled')) {
            $this->call(DemoFamilySeeder::class);
        }
    }
}
