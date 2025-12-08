<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Roles existentes:\n";
foreach (Role::all() as $role) {
    echo "- {$role->name}\n";
}

echo "\nUsuarios con rol 'admin':\n";
$admins = User::role('admin')->get();
if ($admins->isEmpty()) {
    echo "No hay usuarios con rol 'admin'.\n";
} else {
    foreach ($admins as $user) {
        echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
}

echo "\nUsuarios con rol 'administrador' (si existe):\n";
try {
    $administradores = User::role('administrador')->get();
    if ($administradores->isEmpty()) {
        echo "No hay usuarios con rol 'administrador'.\n";
    } else {
        foreach ($administradores as $user) {
            echo "- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
        }
    }
} catch (\Exception $e) {
    echo "El rol 'administrador' no existe en la BD.\n";
}
