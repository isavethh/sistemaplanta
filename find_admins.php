<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Buscando administradores...\n";

$users = User::all();
foreach ($users as $user) {
    $roles = $user->getRoleNames();
    $esAdmin = false;
    foreach ($roles as $role) {
        if (strpos($role, 'admin') !== false) {
            $esAdmin = true;
        }
    }

    if ($user->tipo === 'admin' || $user->role === 'admin' || $user->role === 'administrador') {
        $esAdmin = true;
    }

    if ($esAdmin) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Roles: {$roles}, Tipo: {$user->tipo}, RoleCol: {$user->role}\n";
    }
}
