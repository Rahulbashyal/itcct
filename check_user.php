<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$symbol = '000000';
$password = 'password';

$user = User::withoutGlobalScopes()->where('symbol_number', $symbol)->first();

if ($user) {
    echo "User found: " . $user->name . "\n";
    echo "Hash match: " . (Hash::check($password, $user->password) ? "YES" : "NO") . "\n";
} else {
    echo "User not found\n";
}
