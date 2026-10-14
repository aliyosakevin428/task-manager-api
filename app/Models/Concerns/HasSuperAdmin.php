<?php

namespace App\Models\Concerns;

use Spatie\Permission\PermissionRegistrar;

trait HasSuperAdmin
{
    public function isSuperAdmin(string $guard = 'sanctum'): bool
    {
        $registrar = app(PermissionRegistrar::class);

        $currentTeamId = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId(0);

        try {
            return $this->hasRole('superadmin', $guard);
        } finally {
            $registrar->setPermissionsTeamId($currentTeamId);
        }
    }
}
