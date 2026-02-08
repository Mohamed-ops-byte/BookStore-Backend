<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

function getArg(string $key): ?string {
    global $argv;
    foreach ($argv as $i => $arg) {
        if (str_starts_with($arg, "--{$key}=")) {
            return substr($arg, strlen("--{$key}="));
        }
        if ($arg === "--{$key}" && isset($argv[$i+1])) {
            return $argv[$i+1];
        }
    }
    return null;
}

$email = getArg('email');
$newEmail = getArg('new-email');
$newPassword = getArg('new-password');

if (!$newEmail && !$newPassword) {
    fwrite(STDERR, "Usage: php update-user.php --email <current-email> [--new-email <new-email>] [--new-password <new-password>]\n".
        "Examples:\n".
        "  php update-user.php --email test@example.com --new-email new@example.com --new-password 987654\n".
        "  php update-user.php --email test@example.com --new-password 987654\n");
    exit(1);
}

$user = $email ? User::where('email', $email)->first() : User::first();

if (!$user) {
    fwrite(STDERR, "User not found" . ($email ? ": {$email}" : ". No users in database.") . "\n");
    exit(1);
}

if ($newEmail) {
    // Prevent duplicate emails
    $exists = User::where('email', $newEmail)->where('id', '!=', $user->id)->exists();
    if ($exists) {
        fwrite(STDERR, "Cannot update email: '{$newEmail}' is already taken.\n");
        exit(1);
    }
    $user->email = $newEmail;
}

if ($newPassword) {
    if (strlen($newPassword) < 6) {
        fwrite(STDERR, "Password must be at least 6 characters.\n");
        exit(1);
    }
    $user->password = Hash::make($newPassword);
}

$user->save();

echo "User updated successfully:\n";
$effectiveEmail = $user->email;
echo " - Email: {$effectiveEmail}\n";
if ($newPassword) {
    echo " - Password: (updated)\n";
} else {
    echo " - Password: (unchanged)\n";
}
