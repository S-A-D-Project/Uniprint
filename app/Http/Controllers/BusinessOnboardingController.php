<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BusinessOnboardingController extends Controller
{
    public function show()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $existingEnterprise = null;
        if (Schema::hasTable('enterprises') && Schema::hasColumn('enterprises', 'owner_user_id')) {
            $existingEnterprise = DB::table('enterprises')
                ->where('owner_user_id', $userId)
                ->first();
        }

        if (! $existingEnterprise && Schema::hasTable('staff')) {
            $existingEnterprise = DB::table('staff')
                ->where('user_id', $userId)
                ->first();
        }

        if ($existingEnterprise) {
            return redirect()->route('business.dashboard');
        }

        return view('business.onboarding');
    }

    public function store(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        if (! Schema::hasTable('enterprises')) {
            return back()->withInput()->with('error', 'Database schema is not ready. Please run migrations and try again.');
        }

        $existingEnterprise = null;
        if (Schema::hasColumn('enterprises', 'owner_user_id')) {
            $existingEnterprise = DB::table('enterprises')
                ->where('owner_user_id', $userId)
                ->first();
        }

        if (! $existingEnterprise && Schema::hasTable('staff')) {
            $existingEnterprise = DB::table('staff')
                ->where('user_id', $userId)
                ->first();
        }

        if ($existingEnterprise) {
            return redirect()->route('business.dashboard');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $enterpriseId = (string) Str::uuid();

            $enterpriseData = [
                'enterprise_id' => $enterpriseId,
                'name' => $request->name,
                'address' => $request->address,
                'contact_person' => $request->contact_person,
                'contact_number' => $request->contact_number,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('enterprises', 'owner_user_id')) {
                $enterpriseData['owner_user_id'] = $userId;
            }

            if (Schema::hasColumn('enterprises', 'email')) {
                $enterpriseData['email'] = $request->email;
            }

            if (Schema::hasColumn('enterprises', 'category')) {
                $enterpriseData['category'] = $request->category;
            }

            if (Schema::hasColumn('enterprises', 'is_active')) {
                $enterpriseData['is_active'] = true;
            }

            // Legacy schema compatibility: some DBs still have VAT columns/constraints.
            // If vat_type_id exists and is required, ensure a placeholder vat type exists.
            if (Schema::hasColumn('enterprises', 'vat_type_id')) {
                if (! Schema::hasTable('vat_types')) {
                    throw new \RuntimeException('Database schema mismatch: enterprises.vat_type_id exists but vat_types table is missing.');
                }

                $vatTypeId = DB::table('vat_types')->value('vat_type_id');
                if (! $vatTypeId) {
                    $vatTypeId = (string) Str::uuid();
                    DB::table('vat_types')->insert([
                        'vat_type_id' => $vatTypeId,
                        'type_name' => 'None',
                    ]);
                }
                $enterpriseData['vat_type_id'] = $vatTypeId;
            }

            DB::table('enterprises')->insert($enterpriseData);

            // Legacy compatibility: if a staff table exists, keep a single Owner row.
            if (Schema::hasTable('staff')) {
                $staffData = [
                    'staff_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'enterprise_id' => $enterpriseId,
                    'position' => 'Owner',
                    'department' => 'Management',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('staff', 'staff_name')) {
                    $staffData['staff_name'] = session('user_name') ?? 'Owner';
                }

                DB::table('staff')->insert($staffData);
            }

            DB::commit();

            return redirect()->route('business.dashboard')->with('success', 'Business profile created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Business onboarding failed', [
                'user_id' => $userId,
                'exception' => $e,
            ]);

            $message = 'Failed to create business profile. Please try again.';
            if (config('app.debug')) {
                $message = $message . ' ' . $e->getMessage();
            }

            return back()->withInput()->with('error', $message);
        }
    }
}
