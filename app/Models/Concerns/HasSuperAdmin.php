<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

trait HasSuperAdmin
{
    public function isSuperAdmin(string $guard = 'sanctum'): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'superadmin')
            ->where('roles.guard_name', $guard)
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', $this->getMorphClass())
            ->where('model_has_roles.team_id', 0)
            ->exists();
    }
}
