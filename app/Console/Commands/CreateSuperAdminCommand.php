<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'famledger:create-super-admin
                            {--name= : Display name}
                            {--email= : Login email}
                            {--password= : Password (min 8 chars)}';

    protected $description = 'Create one user with Super Administrator role (highest privileges).';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Name', 'Super Administrator');
        $email = $this->option('email') ?? $this->ask('Email', 'admin@famledger.local');
        $password = $this->option('password') ?? $this->secret('Password (min 8 characters)');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'status' => User::STATUS_ACTIVE,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info("User created: {$user->email}");
        } else {
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'status' => User::STATUS_ACTIVE,
            ]);
            $this->info("Existing user updated: {$user->email}");
        }

        if (! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
            $this->info('Assigned role: Super Admin');
        } else {
            $this->info('User already has Super Admin role.');
        }

        $this->newLine();
        $this->info('Super Administrator ready. Log in with:');
        $this->line("  Email: {$user->email}");
        $this->line('  Password: (the one you entered)');

        return self::SUCCESS;
    }
}
