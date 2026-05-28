<?php

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la linea de comandos.');
}

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('role', 'admin')->first();

if (!$user) {
    echo "No se encontro un usuario administrador.\n";
    exit(1);
}

$user->password = app('hash')->make('admin123');
$user->save();
echo "Password del usuario {$user->email} reseteado a: admin123\n";
