<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.login');
    }

    public function showAdminLogin()
    {
        if (Auth::check() || session('user_id')) {
            return $this->redirectToDashboard();
        }
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        try {
            $request->merge([
                'username' => is_string($request->input('username')) ? trim($request->input('username')) : $request->input('username'),
            ]);

            $request->validate([
                'username' => 'required|string|max:100',
                'password' => 'required|string|min:8|max:255',
            ]);

            $intendedRoleType = $request->input('role_type');

            // Try to find by username first, then by email
            $loginRecord = DB::table('login')
                ->where('username', $request->input('username'))
                ->first();

        // If not found by username, try to find user by email and get their login record
        if (!$loginRecord) {
            $user = DB::table('users')
                ->where('email', $request->input('username'))
                ->first();

            if ($user) {
                $loginRecord = DB::table('login')
                    ->where('user_id', $user->user_id)
                    ->first();
            }
        }

        if ($loginRecord && Hash::check($request->input('password'), $loginRecord->password)) {
            // Get user record
            $user = DB::table('users')
                ->where('user_id', $loginRecord->user_id)
                ->first();

            if ($user) {
                if ($intendedRoleType === 'admin') {
                    $role = DB::table('roles')
                        ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                        ->where('roles.user_id', $user->user_id)
                        ->first();

                    if (($role?->user_role_type ?? null) !== 'admin') {
                        return back()->withErrors([
                            'username' => 'The provided credentials do not match our records.',
                        ])->withInput($request->only('username'));
                    }
                }

                // Secure session management
                $request->session()->regenerate();
                $request->session()->put([
                    'user_id' => $user->user_id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_activity' => now(),
                ]);
                $request->session()->regenerateToken();

                try {
                    DB::table('audit_logs')->insert([
                        'log_id' => (string) Str::uuid(),
                        'user_id' => $user->user_id,
                        'action' => 'login',
                        'entity_type' => 'auth',
                        'entity_id' => null,
                        'description' => 'User logged in',
                        'old_values' => null,
                        'new_values' => json_encode(['intended_role_type' => $intendedRoleType]),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to write audit log (login)', ['error' => $e->getMessage()]);
                }

                return $this->redirectToDashboard();
            }
        }

        return back()->withErrors([
                'username' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('username'));
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            \Log::warning('Login validation failed', [
                'username' => $request->input('username'),
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);
            throw $e;
            
        } catch (\Exception $e) {
            // Log unexpected errors
            \Log::error('Login error occurred', [
                'username' => $request->input('username'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            
            return back()->withErrors([
                'username' => 'An error occurred during login. Please try again.',
            ])->withInput($request->only('username'));
        }
    }

    public function logout(Request $request)
    {
        try {
            $userId = session('user_id');
            if ($userId) {
                DB::table('audit_logs')->insert([
                    'log_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'action' => 'logout',
                    'entity_type' => 'auth',
                    'entity_id' => null,
                    'description' => 'User logged out',
                    'old_values' => null,
                    'new_values' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to write audit log (logout)', ['error' => $e->getMessage()]);
        }

        $request->session()->flush();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showRegister()
    {
        return redirect()->route('login', ['tab' => 'signup']);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email|max:255',
            'username' => 'required|string|unique:login,username|max:100',
            'password' => 'required|string|min:8|confirmed',
            'role_type' => 'nullable|string',
            'terms_accepted' => 'accepted',
        ]);

        $desiredRoleType = $request->input('role_type', 'customer');
        if ($desiredRoleType === 'business') {
            $desiredRoleType = 'business_user';
        }
        if (!in_array($desiredRoleType, ['customer', 'business_user'], true)) {
            $desiredRoleType = 'customer';
        }

        // Create user record
        $userId = Str::uuid();

        $position = $desiredRoleType === 'business_user' ? 'Owner' : 'Customer';
        $department = $desiredRoleType === 'business_user' ? 'Management' : 'External';
        DB::table('users')->insert([
            'user_id' => $userId,
            'name' => $request->name,
            'email' => $request->email,
            'position' => $position,
            'department' => $department,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create login record
        DB::table('login')->insert([
            'login_id' => Str::uuid(),
            'user_id' => $userId,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleType = DB::table('role_types')
            ->where('user_role_type', $desiredRoleType)
            ->first();

        $roleTypeId = $roleType?->role_type_id;
        if (!$roleTypeId) {
            $roleTypeId = (string) Str::uuid();
            DB::table('role_types')->insert([
                'role_type_id' => $roleTypeId,
                'user_role_type' => $desiredRoleType,
            ]);
        }

        DB::table('roles')->insert([
            'role_id' => Str::uuid(),
            'user_id' => $userId,
            'role_type_id' => $roleTypeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log in the user (match login() session behavior)
        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => $userId,
            'user_name' => $request->name,
            'user_email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity' => now(),
        ]);
        $request->session()->regenerateToken();

        return $this->redirectToDashboard();
    }

    private function redirectToDashboard()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }

        // Get user's role
        $role = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('roles.user_id', $userId)
            ->first();

        if ($role) {
            switch ($role->user_role_type) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'business_user':
                    $hasEnterprise = false;

                    if (\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'owner_user_id')) {
                        $hasEnterprise = DB::table('enterprises')
                            ->where('owner_user_id', $userId)
                            ->exists();
                    }

                    if (! $hasEnterprise && \Illuminate\Support\Facades\Schema::hasTable('staff')) {
                        // Backward-compatibility for legacy data
                        $hasEnterprise = DB::table('staff')
                            ->where('user_id', $userId)
                            ->exists();
                    }
                    if (!$hasEnterprise) {
                        return redirect()->route('business.onboarding');
                    }
                    return redirect()->route('business.dashboard');
                case 'customer':
                    return redirect()->route('customer.dashboard');
                default:
                    return redirect()->route('home');
            }
        }

        // Default to customer dashboard for authenticated users
        return redirect()->route('customer.dashboard');
    }
}
