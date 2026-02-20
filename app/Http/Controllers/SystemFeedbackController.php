<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SystemFeedbackController extends Controller
{
    public function store(Request $request)
    {
        $userId = session('user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        if (! schema_has_table('system_feedback')) {
            return redirect()->back()->with('error', 'Feedback is not available. Please run migrations and try again.');
        }

        $request->validate([
            'category' => 'required|string|max:50',
            'rating' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        DB::table('system_feedback')->insert([
            'feedback_id' => (string) Str::uuid(),
            'user_id' => (string) $userId,
            'category' => $request->string('category')->toString(),
            'rating' => $request->filled('rating') ? $request->string('rating')->toString() : null,
            'subject' => $request->string('subject')->toString(),
            'message' => $request->string('message')->toString(),
            'status' => 'new',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Feedback submitted. Thank you!');
    }
}
