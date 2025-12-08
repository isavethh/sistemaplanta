<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Roles en DB:\n";
foreach (Role::all() as $role) {
    echo "- {$role->name}\n";
}

echo "\nUsuarios y sus roles:\n";
foreach (User::all() as $user) {
    $roles = $user->getRoleNames();
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Roles: {$roles}, Tipo: {$user->tipo}, RoleCol: {$user->role}\n";
}
