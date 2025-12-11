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

        // Si no hay restricción de rol, todos pueden verlo
        if (empty($item['role'])) {
            return false;
        }

        $roles = is_array($item['role']) ? $item['role'] : [$item['role']];

        // Admins pueden ver todo EXCEPTO elementos específicos de otros roles
        if ($user->hasRole('admin')) {
            // Permitir si el item tiene el rol 'admin' o no tiene rol específico
            if (in_array('admin', $roles)) {
                return false;
            }
            // Ocultar si es específico para transportista o almacen
            if (in_array('transportista', $roles) || in_array('almacen', $roles)) {
                return true;
            }
            // Permitir si no tiene restricción de rol
            return false;
        }

        // Para otros usuarios, verificar si tienen el rol requerido
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return false;
            }
        }

        return true;
    }
}
