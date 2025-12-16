<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;

class RoleFilter implements FilterInterface
{
    /**
     * Transforms a menu item. Add the restricted property to a menu item
     * when the user does not have the required role.
     *
     * @param  array  $item  A menu item
     * @return array The transformed menu item
     */
    public function transform($item)
    {
        // Verificar si el item está restringido (aplica tanto a headers como a items normales)
        if ($this->isRestricted($item)) {
            $item['restricted'] = true;
        }

        return $item;
    }

    /**
     * Checks if a menu item is restricted for the current user.
     *
     * @param  array  $item  A menu item
     * @return bool
     */
    protected function isRestricted($item)
    {
        if (! Auth::check()) {
            return true;
        }

        $user = Auth::user();

        // Si el item tiene la propiedad 'exclude_admin', ocultarlo para admin
        if (isset($item['exclude_admin']) && $item['exclude_admin'] === true) {
            if ($user->hasRole('admin')) {
                return true; // Restringir para admin
            }
        }

        // Super users should see everything (admin, administrador, super-admin)
        // EXCEPTO los elementos marcados como solo para operador (sin otros roles)
        if ($user->hasRole('super-admin') || $user->hasRole('administrador') || $user->hasRole('admin')) {
            // Si el item está marcado solo para operador, ocultarlo para admin
            if (!empty($item['role']) && $item['role'] === 'operador' && !isset($item['show_to_admin'])) {
                return true; // Restringir elementos solo de operador para admin
            }
            return false;
        }

        // Si el item no tiene restricción de rol, mostrarlo
        if (empty($item['role'])) {
            return false;
        }

        $roles = is_array($item['role']) ? $item['role'] : [$item['role']];

        // Verificar si el usuario tiene alguno de los roles requeridos
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return false; // No restringir si tiene el rol
            }
        }

        // Si no tiene ninguno de los roles requeridos, restringir
        return true;
    }
}
