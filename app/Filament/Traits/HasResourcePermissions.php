<?php

namespace App\Filament\Traits;

/**
 * Aplica control de permisos Spatie en recursos Filament.
 * Cada recurso define $permissionPrefix (ej: 'inmuebles').
 * Se mapea a: ver_inmuebles, crear_inmuebles, editar_inmuebles, eliminar_inmuebles.
 */
trait HasResourcePermissions
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('ver_' . static::$permissionPrefix) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('crear_' . static::$permissionPrefix) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('editar_' . static::$permissionPrefix) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('eliminar_' . static::$permissionPrefix) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('eliminar_' . static::$permissionPrefix) ?? false;
    }
}
