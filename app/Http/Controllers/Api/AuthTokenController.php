<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller
{
    public function issueToken(Request $request)
    {
        $request->merge([
            'username' => is_string($request->input('username')) ? trim($request->input('username')) : $request->input('username'),
        ]);

        $validated = $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|min:8|max:255',
            'device_name' => 'nullable|string|max:100',
        ]);

        $loginRecord = DB::table('login')
            ->where('username', $validated['username'])
            ->first();

        if (!$loginRecord) {
            $userRow = DB::table('users')
                ->where('email', $validated['username'])
                ->first();

            if ($userRow) {
                $loginRecord = DB::table('login')
                    ->where('user_id', $userRow->user_id)
                    ->first();
            }
        }

        if (!$loginRecord || !Hash::check($validated['password'], $loginRecord->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        $user = User::find($loginRecord->user_id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $deviceName = $validated['device_name'] ?? 'api';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'token_type' => 'Bearer',
            'token' => $token,
            'user_id' => $user->user_id,
        ]);
    }

    public function revokeCurrentToken(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
