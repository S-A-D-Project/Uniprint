<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIDesignController extends Controller
{
    /**
     * Show the AI design tool page
     */
    public function index()
    {
        return view('ai-design.index');
    }

    /**
     * Generate AI design based on prompt
     */
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'style' => 'nullable|string|in:modern,classic,minimalist,vintage',
            'size' => 'nullable|string|in:square,landscape,portrait'
        ]);

        try {
            // Simulate AI generation (replace with actual AI API)
            $prompt = $request->prompt;
            $style = $request->style ?? 'modern';
            $size = $request->size ?? 'square';
            
            // For demo purposes, return a placeholder image
            // In production, integrate with OpenAI DALL-E, Midjourney, or similar
            $imageUrl = $this->generateMockImage($prompt, $style, $size);
            
            // Save to temporary storage
            $filename = 'ai-design-' . Str::uuid() . '.png';
            $path = 'temp/designs/' . $filename;
            
            // Download and store the image
            $imageContents = Http::get($imageUrl)->body();
            Storage::disk('public')->put($path, $imageContents);
            
            return response()->json([
                'success' => true,
                'image_url' => Storage::url($path),
                'filename' => $filename,
                'prompt' => $prompt,
                'style' => $style,
                'size' => $size
            ]);
            
        } catch (\Exception $e) {
            Log::error('AI Design Generation Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate design. Please try again.'
            ], 500);
        }
    }

    /**
     * Save generated design to user's design assets
     */
    public function save(Request $request)
    {
        $request->validate([
            'image_url' => 'required|string',
            'filename' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to save designs'
                ], 401);
            }

            // Move from temp to permanent storage
            $tempPath = str_replace('/storage/', 'public/', $request->image_url);
            $permanentPath = 'designs/' . $userId . '/' . $request->filename;
            
            if (Storage::exists($tempPath)) {
                Storage::move($tempPath, $permanentPath);
                
                // Save to database
                $designId = Str::uuid();
                \DB::table('user_designs')->insert([
                    'design_id' => $designId,
                    'user_id' => $userId,
                    'title' => $request->title,
                    'description' => $request->description,
                    'file_path' => $permanentPath,
                    'file_url' => Storage::url($permanentPath),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Design saved successfully!',
                    'design_id' => $designId
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Design file not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('AI Design Save Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save design'
            ], 500);
        }
    }

    /**
     * Get user's saved designs
     */
    public function myDesigns()
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return redirect()->route('login')->with('error', 'Please login to view your designs');
            }

            $designs = \DB::table('user_designs')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(12);

            return view('ai-design.my-designs', compact('designs'));
            
        } catch (\Exception $e) {
            Log::error('AI Design List Error: ' . $e->getMessage());
            
            return view('ai-design.my-designs', ['designs' => collect()]);
        }
    }

    /**
     * Delete a saved design
     */
    public function delete($designId)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $design = \DB::table('user_designs')
                ->where('design_id', $designId)
                ->where('user_id', $userId)
                ->first();

            if (!$design) {
                return response()->json(['success' => false, 'message' => 'Design not found'], 404);
            }

            // Delete file from storage
            if (Storage::exists($design->file_path)) {
                Storage::delete($design->file_path);
            }

            // Delete from database
            \DB::table('user_designs')
                ->where('design_id', $designId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Design deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('AI Design Delete Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete design'
            ], 500);
        }
    }

    /**
     * Generate mock image for demo purposes
     * Replace with actual AI API integration
     */
    private function generateMockImage($prompt, $style, $size)
    {
        // Demo images based on style
        $demoImages = [
            'modern' => 'https://images.unsplash.com/photo-1634942537034-2531766767d1?w=800&h=800&fit=crop',
            'classic' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=800&h=800&fit=crop',
            'minimalist' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=800&h=800&fit=crop',
            'vintage' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=800&h=800&fit=crop'
        ];

        return $demoImages[$style] ?? $demoImages['modern'];
    }
}
