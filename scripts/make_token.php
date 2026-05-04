<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$u = User::where('email', 'admin@example.com')->first();
if (! $u) {
    echo "NO_USER";
    exit(1);
}
$token = $u->createToken('cli-token')->plainTextToken;
echo $token;
