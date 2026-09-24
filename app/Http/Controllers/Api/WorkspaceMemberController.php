<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    public function index(Request $request, Workspace $workspace)
    {
        $members = $workspace->members()
            ->select('users.id', 'users.name', 'users.email')
            ->withPivot(['role'])
            ->orderBy('users.name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->pivot->role
            ]);

        return response()->json([
            'data' => [
                'members' => $members,
            ]
        ]);

    }
}
