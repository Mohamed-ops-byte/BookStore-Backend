<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Get first user
$user = User::first();

if ($user) {
    $user->password = Hash::make('123456');
    $user->save();
    
    echo "Password updated successfully for user: " . $user->email . "\n";
    echo "New password: 123456\n";
} else {
    echo "No users found in database.\n";
}
