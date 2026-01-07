<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');

        if (!$userId) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return redirect()->route('admin.login')->with('error', 'Please login to continue');
            }
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        // Check if user is already authenticated in Laravel's auth system
        if (!Auth::check()) {
            // Try to authenticate the user with Laravel's auth system
            $user = DB::table('users')->where('user_id', $userId)->first();

            if (!$user) {
                // User doesn't exist, clear session and redirect to login
                session()->flush();
                return redirect()->route('login')->with('error', 'Session expired. Please login again.');
            }

            // Manually authenticate the user with Laravel's auth system
            // Since we're using UUID as primary key, we need to find the user and authenticate manually
            $laravelUser = \App\Models\User::find($userId);

            if ($laravelUser) {
                Auth::login($laravelUser);
            } else {
                // User exists in database but not in Eloquent model (shouldn't happen)
                session()->flush();
                return redirect()->route('login')->with('error', 'Authentication error. Please login again.');
            }
        }

        return $next($request);
    }
}
