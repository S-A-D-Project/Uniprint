<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SyncLaravelAuthFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionUserId = session('user_id');

        if ($sessionUserId && !Auth::check()) {
            Auth::loginUsingId($sessionUserId);
        }

        if (Auth::check() && !$sessionUserId) {
            $user = Auth::user();
            if ($user && !empty($user->user_id)) {
                session([
                    'user_id' => $user->user_id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_activity' => now(),
                ]);
            }
        }

        if (Auth::check() && $sessionUserId) {
            $dbUser = Auth::user();
            if (!$dbUser || empty($dbUser->user_id) || (string) $dbUser->user_id !== (string) $sessionUserId) {
                $freshUser = DB::table('users')->where('user_id', $sessionUserId)->first();
                if ($freshUser && !empty($freshUser->user_id)) {
                    Auth::loginUsingId($sessionUserId);
                }
            }
        }

        return $next($request);
    }
}
