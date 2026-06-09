<?php
require 'C:\laragon\www\Shabakat_Rewards\backend\vendor\autoload.php';
$app = require_once 'C:\laragon\www\Shabakat_Rewards\backend\bootstrap\app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\User;

$user = User::where('phone', '777211694')->first();
if (!$user) { echo "User not found\n"; exit(1); }

echo "Current balance: " . $user->points_balance . "\n";

// -45 current + 20 transfer = -25 correct balance
$correct = -25;
$user->points_balance = $correct;
$user->save();

echo "Fixed to: " . $user->fresh()->points_balance . "\n";
