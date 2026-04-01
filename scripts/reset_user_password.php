<?php

/**
 * Reset a user's password using the User model (correct bcrypt hashing).
 *
 * Usage: php scripts/reset_user_password.php email@example.com "NewPassword!"
 */

declare(strict_types=1);
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

$base = dirname(__DIR__);
require $base.'/vendor/autoload.php';

$app = require $base.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if ($argc < 3) {
    fwrite(STDERR, "Usage: php scripts/reset_user_password.php email@example.com \"Password\"\n");
    exit(1);
}

$email = $argv[1];
$plain = $argv[2];

$user = User::query()->where('email', $email)->first();

if (! $user) {
    fwrite(STDERR, "No user found for: {$email}\n");
    exit(1);
}

$user->forceFill([
    'password' => $plain,
    'must_change_password' => true,
])->save();

echo "Password updated for {$email} (must_change_password=true).\n";
