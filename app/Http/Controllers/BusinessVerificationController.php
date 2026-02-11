<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BusinessVerificationController extends Controller
{
    private function getUserEnterprise(string $userId)
    {
        if (!Schema::hasTable('enterprises')) {
            return null;
        }

        $enterprise = null;
        if (Schema::hasColumn('enterprises', 'owner_user_id')) {
            $enterprise = DB::table('enterprises')
                ->where('owner_user_id', $userId)
                ->first();
        }

        if (!$enterprise && Schema::hasTable('staff')) {
            $enterpriseId = DB::table('staff')->where('user_id', $userId)->value('enterprise_id');
            if ($enterpriseId) {
                $enterprise = DB::table('enterprises')->where('enterprise_id', $enterpriseId)->first();
            }
        }

        return $enterprise;
    }

    public function show()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $enterprise = $this->getUserEnterprise($userId);
        if (!$enterprise) {
            return redirect()->route('business.onboarding');
        }

        return view('business.verification', compact('enterprise'));
    }

    public function store(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $enterprise = $this->getUserEnterprise($userId);
        if (!$enterprise) {
            return redirect()->route('business.onboarding');
        }

        if (!Schema::hasColumn('enterprises', 'verification_document_path') || !Schema::hasColumn('enterprises', 'verification_submitted_at')) {
            return redirect()->back()->with('error', 'Business verification proof is not available. Please run migrations and try again.');
        }

        $rules = [
            'verification_document' => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf',
        ];

        if (Schema::hasColumn('enterprises', 'verification_notes')) {
            $rules['verification_notes'] = 'nullable|string|max:2000';
        }

        $request->validate($rules);

        $disk = config('filesystems.default', 'public');
        $newPath = $request->file('verification_document')->store('business-verification', $disk);

        $update = [
            'verification_document_path' => $newPath,
            'verification_submitted_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('enterprises', 'verification_notes')) {
            $update['verification_notes'] = $request->input('verification_notes');
        }

        if (Schema::hasColumn('enterprises', 'is_verified')) {
            $update['is_verified'] = false;
        }

        if (Schema::hasColumn('enterprises', 'verified_at')) {
            $update['verified_at'] = null;
        }

        if (Schema::hasColumn('enterprises', 'verified_by_user_id')) {
            $update['verified_by_user_id'] = null;
        }

        DB::table('enterprises')->where('enterprise_id', $enterprise->enterprise_id)->update($update);

        if (!empty($enterprise->verification_document_path)) {
            $disk = config('filesystems.default', 'public');
            try {
                if (Storage::disk($disk)->exists($enterprise->verification_document_path)) {
                    Storage::disk($disk)->delete($enterprise->verification_document_path);
                } elseif (Storage::disk('public')->exists($enterprise->verification_document_path)) {
                    Storage::disk('public')->delete($enterprise->verification_document_path);
                }
            } catch (\Throwable $e) {
            }
        }

        return redirect()->route('business.pending')->with('success', 'Verification proof submitted. Your account is pending admin review.');
    }
}
