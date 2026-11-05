<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetWorkspaceContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Workspace $workspace */

        $workspace = $request->route('workspace');
        $user = $request->user();

        if (!$workspace || !$user) {
            abort(404);
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        $isMember = $workspace->members()
            ->where('user.id', $user->id)
            ->exists();

        abort_unless($isMember, 403, 'You are not a member of this workspace.');

        return $next($request);

    }
}
