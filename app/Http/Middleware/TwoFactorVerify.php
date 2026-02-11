<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorVerify
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');
        if (!$userId) {
            return $next($request);
        }

        if ($request->is('verify') || $request->is('verify/*') || $request->is('logout')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return $next($request);
        }

        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();

        $roleType = (string) ($role?->user_role_type ?? '');
        if ($roleType === 'admin') {
            return $next($request);
        }

        $user = DB::table('users')
            ->select(['user_id', 'two_factor_enabled', 'two_factor_code', 'two_factor_expires_at'])
            ->where('user_id', $userId)
            ->first();

        if (
            !empty($user)
            && !empty($user->two_factor_enabled)
            && isset($user->two_factor_code)
            && (string) $user->two_factor_code !== ''
        ) {
            $expiresAt = $user->two_factor_expires_at ?? null;
            $isExpired = true;
            if ($expiresAt) {
                try {
                    $isExpired = Carbon::parse($expiresAt)->isPast();
                } catch (\Throwable $e) {
                    $isExpired = true;
                }
            }

            if ($isExpired) {
                DB::table('users')->where('user_id', $userId)->update([
                    'two_factor_code' => null,
                    'two_factor_expires_at' => null,
                    'updated_at' => now(),
                ]);
                session()->forget('two_factor_intended');
            } else {
                session(['two_factor_intended' => $request->fullUrl()]);
                return redirect()->route('two-factor.verify');
            }
        } else {
            session()->forget('two_factor_intended');
        }

        return $next($request);
    }
}
