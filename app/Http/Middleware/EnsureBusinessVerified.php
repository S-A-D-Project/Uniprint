<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        if (! Schema::hasTable('enterprises')) {
            return $next($request);
        }

        $hasIsVerifiedColumn = Schema::hasColumn('enterprises', 'is_verified');
        $selectColumns = ['enterprise_id'];
        if ($hasIsVerifiedColumn) {
            $selectColumns[] = 'is_verified';
        }

        $enterprise = null;
        if (Schema::hasColumn('enterprises', 'owner_user_id')) {
            $enterprise = DB::table('enterprises')
                ->where('owner_user_id', $userId)
                ->select($selectColumns)
                ->first();
        }

        if (! $enterprise && Schema::hasTable('staff')) {
            $enterpriseId = DB::table('staff')->where('user_id', $userId)->value('enterprise_id');
            if ($enterpriseId) {
                $enterprise = DB::table('enterprises')
                    ->where('enterprise_id', $enterpriseId)
                    ->select($selectColumns)
                    ->first();
            }
        }

        if ($enterprise && $hasIsVerifiedColumn && empty($enterprise->is_verified)) {
            if (
                ! $request->routeIs('business.pending')
                && ! $request->routeIs('business.onboarding')
                && ! $request->routeIs('business.onboarding.store')
                && ! $request->routeIs('business.verification')
                && ! $request->routeIs('business.verification.store')
                && ! $request->routeIs('business.notifications')
                && ! $request->routeIs('business.notifications.read')
                && ! $request->routeIs('logout')
                && ! $request->routeIs('home')
                && ! $request->routeIs('enterprises.index')
                && ! $request->routeIs('enterprises.show')
                && ! $request->routeIs('terms')
            ) {
                return redirect()->route('business.pending');
            }
        }

        return $next($request);
    }
}
