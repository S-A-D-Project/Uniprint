<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiGenerationLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $limit = $this->getDailyLimitForUser($userId);
        if ($limit === null) {
            return $next($request);
        }

        if (! Schema::hasTable('ai_generation_daily_usages')) {
            return response()->json([
                'success' => false,
                'message' => 'AI usage tracking table is not available. Please run migrations.',
            ], 500);
        }

        $today = now()->toDateString();

        $allowed = false;
        $remaining = 0;

        DB::transaction(function () use ($userId, $today, $limit, &$allowed, &$remaining) {
            $row = DB::table('ai_generation_daily_usages')
                ->where('user_id', $userId)
                ->where('usage_date', $today)
                ->lockForUpdate()
                ->first();

            $current = (int) ($row->generation_count ?? 0);

            if ($current >= $limit) {
                $allowed = false;
                $remaining = 0;
                return;
            }

            $newCount = $current + 1;

            if ($row) {
                DB::table('ai_generation_daily_usages')
                    ->where('user_id', $userId)
                    ->where('usage_date', $today)
                    ->update([
                        'generation_count' => $newCount,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('ai_generation_daily_usages')->insert([
                    'user_id' => $userId,
                    'usage_date' => $today,
                    'generation_count' => $newCount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $allowed = true;
            $remaining = max(0, $limit - $newCount);
        });

        if (! $allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Daily AI generation limit reached. Please try again tomorrow.',
                'error_code' => 'AI_DAILY_LIMIT_REACHED',
                'daily_limit' => $limit,
                'remaining_today' => 0,
            ], 429);
        }

        $response = $next($request);

        $payload = [
            'daily_limit' => $limit,
            'remaining_today' => $remaining,
        ];

        $response->headers->set('X-AI-Daily-Limit', (string) $limit);
        $response->headers->set('X-AI-Remaining-Today', (string) $remaining);

        return $response;
    }

    private function getDailyLimitForUser(string $userId): ?int
    {
        $roleType = null;

        try {
            $roleType = DB::table('roles')
                ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
                ->where('roles.user_id', $userId)
                ->value('role_types.user_role_type');
        } catch (\Throwable $e) {
            $roleType = null;
        }

        if ($roleType === 'business_user' || $roleType === 'admin') {
            return 50;
        }

        return 5;
    }
}
