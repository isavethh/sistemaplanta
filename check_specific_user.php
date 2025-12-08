<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'planta@sistema.com';
$user = User::where('email', $email)->first();

if ($user) {
    echo "Usuario encontrado: {$user->name} ({$user->email})\n";
    echo "ID: {$user->id}\n";
    echo "Roles (Spatie): " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
    echo "Columna 'role': {$user->role}\n";
    echo "Columna 'tipo': {$user->tipo}\n"; // A veces se usa 'tipo' en lugar de roles
} else {
    echo "No se encontró el usuario con email: $email\n";

    // Listar todos los usuarios para ver si hay alguno parecido
    echo "\nListado de usuarios disponibles:\n";
    foreach (User::all() as $u) {
        echo "- {$u->email} (Rol: {$u->role})\n";
    }
}
