<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserReportController extends Controller
{
    public function store(Request $request)
    {
        $userId = session('user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        if (! schema_has_table('user_reports')) {
            return redirect()->back()->with('error', 'Reporting is not available. Please run migrations and try again.');
        }

        $request->validate([
            'entity_type' => 'required|string|in:enterprise,service',
            'enterprise_id' => 'nullable|uuid',
            'service_id' => 'nullable|uuid',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $entityType = $request->string('entity_type')->toString();
        $enterpriseId = $request->string('enterprise_id')->toString();
        $serviceId = $request->string('service_id')->toString();

        if ($entityType === 'enterprise') {
            if (! $enterpriseId) {
                return redirect()->back()->with('error', 'Invalid report target.');
            }
            if (schema_has_table('enterprises') && ! DB::table('enterprises')->where('enterprise_id', $enterpriseId)->exists()) {
                return redirect()->back()->with('error', 'Enterprise not found.');
            }
            $serviceId = '';
        }

        if ($entityType === 'service') {
            if (! $serviceId) {
                return redirect()->back()->with('error', 'Invalid report target.');
            }
            if (schema_has_table('services') && ! DB::table('services')->where('service_id', $serviceId)->exists()) {
                return redirect()->back()->with('error', 'Service not found.');
            }
            $enterpriseId = '';
        }

        DB::table('user_reports')->insert([
            'report_id' => (string) Str::uuid(),
            'reporter_id' => (string) $userId,
            'enterprise_id' => $enterpriseId ?: null,
            'service_id' => $serviceId ?: null,
            'reason' => $request->string('reason')->toString(),
            'description' => $request->filled('description') ? $request->string('description')->toString() : null,
            'status' => 'open',
            'resolved_by' => null,
            'resolved_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Report submitted. Thank you.');
    }
}
