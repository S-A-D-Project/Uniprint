<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BusinessOnboardingController extends Controller
{
    public function show()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $existingEnterprise = DB::table('staff')
            ->where('user_id', $userId)
            ->first();

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

        $existingEnterprise = DB::table('staff')
            ->where('user_id', $userId)
            ->first();

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

            if (Schema::hasColumn('enterprises', 'email')) {
                $enterpriseData['email'] = $request->email;
            }

            if (Schema::hasColumn('enterprises', 'category')) {
                $enterpriseData['category'] = $request->category;
            }

            if (Schema::hasColumn('enterprises', 'is_active')) {
                $enterpriseData['is_active'] = true;
            }

            DB::table('enterprises')->insert($enterpriseData);

            DB::table('staff')->insert([
                'staff_id' => (string) Str::uuid(),
                'user_id' => $userId,
                'enterprise_id' => $enterpriseId,
                'position' => 'Owner',
                'department' => 'Management',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('business.dashboard')->with('success', 'Business profile created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create business profile. Please try again.');
        }
    }
}
