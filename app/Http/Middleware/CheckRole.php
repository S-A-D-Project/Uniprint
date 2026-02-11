<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $userId = Auth::user()?->user_id ?? session('user_id');

        if (!$userId) {
            if ($request->expectsJson() || $request->ajax() || $request->is('api') || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
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

        $allowedRoles = array_values(array_filter(array_map('trim', preg_split('/[|,]/', $role) ?: [])));
        if (!$allowedRoles) {
            $allowedRoles = [$role];
        }

        if (!$userRole || !in_array($userRole->user_role_type, $allowedRoles, true)) {
            if ($request->expectsJson() || $request->ajax() || $request->is('api') || $request->is('api/*')) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
