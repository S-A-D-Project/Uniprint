<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $userId = session('user_id');

        if (!$userId) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return redirect()->route('admin.login');
            }
            return redirect()->route('login');
        }

        // Get user's role
        $userRole = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();

        if (!$userRole || $userRole->user_role_type !== $role) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
