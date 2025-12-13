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
     * Generate AI design based on prompt using Gemini API
     */
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'style' => 'nullable|string|in:modern,classic,minimalist,vintage,corporate,creative',
            'size' => 'nullable|string|in:square,landscape,portrait,business-card,flyer,poster',
            'design_type' => 'nullable|string|in:business-card,flyer,poster,brochure,logo,banner',
            'color_scheme' => 'nullable|string|max:50',
        ]);

        try {
            $prompt = $request->prompt;
            $style = $request->style ?? 'modern';
            $size = $request->size ?? 'square';
            $designType = $request->design_type ?? null;
            $colorScheme = $request->color_scheme ?? null;
            
            $geminiApiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
            $imageContents = null;

            if (!$geminiApiKey) {
                Log::warning('Gemini API key not configured. Falling back to mock image.');
            } else {
                $imageData = $this->generateWithGemini($prompt, $style, $size, $geminiApiKey, $designType, $colorScheme);
                if ($imageData) {
                    $imageContents = base64_decode($imageData);
                }
            }

            // Fallback to mock image if API key is missing or API call fails
            if (!$imageContents) {
                Log::info('Falling back to mock image generation.');
                $mockImageUrl = $this->generateMockImage($prompt, $style, $size);
                $imageContents = Http::timeout(30)->get($mockImageUrl)->body();
            }
            
            // Save to temporary storage
            $filename = 'ai-design-' . Str::uuid() . '.png';
            $path = 'temp/designs/' . $filename;
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
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the design. Please try again later.'
            ], 500);
        }
    }

    /**
     * Generate an image directly using Google's Imagen API.
     */
    private function generateWithGemini($prompt, $style, $size, $apiKey, $designType = null, $colorScheme = null)
    {
        try {
            $enhancedPrompt = $this->enhancePromptForImagen($prompt, $style, $size, $designType, $colorScheme);
            $dimensions = $this->getSizeDimensions($size);

            Log::info('Generating image with Imagen API. Prompt: ' . $enhancedPrompt);

            $model = config('services.gemini.imagen_model', 'imagen-4.0-generate-001');

            $response = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:predict", [
                'instances' => [
                    ['prompt' => $enhancedPrompt]
                ],
                'parameters' => [
                    'sampleCount' => 1,
                    'aspectRatio' => $dimensions['aspectRatio'],
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['predictions'][0]['bytesBase64Encoded'])) {
                    Log::info('Successfully generated image with Imagen API.');
                    // Return the base64 encoded image data
                    return $data['predictions'][0]['bytesBase64Encoded'];
                }
            }

            Log::warning('Imagen API response unsuccessful: ' . $response->status());
            Log::warning('Imagen API response body: ' . $response->body());
            return null; // Return null on failure

        } catch (\Exception $e) {
            Log::error('Imagen API Error: ' . $e->getMessage());
            Log::error('Imagen API Error Trace: ' . $e->getTraceAsString());
            return null; // Return null on exception
        }
    }

    /**
     * Enhance prompt for better Imagen results.
     */
    private function enhancePromptForImagen($prompt, $style, $size, $designType = null, $colorScheme = null)
    {
        $styleEnhancements = [
            'modern' => 'clean lines, geometric shapes, simple color palette, high quality, 4k',
            'classic' => 'elegant, ornate details, balanced composition, timeless feel, high quality, 4k',
            'minimalist' => 'ultra-minimalist, simple, clean, sparse, generous white space, high quality, 4k',
            'vintage' => 'retro aesthetic, distressed textures, nostalgic color palette, high quality, 4k',
            'corporate' => 'professional, business-oriented, sharp, clean, brand-focused, high quality, 4k',
            'creative' => 'artistic, vibrant colors, abstract elements, imaginative, high quality, 4k',
        ];

        $styleDesc = $styleEnhancements[$style] ?? '';

        $designPart = $designType ? "{$designType}," : '';
        $colorPart = $colorScheme ? "{$colorScheme} color scheme," : '';

        return trim("{$designPart} {$prompt}, {$style} style, {$colorPart} {$styleDesc}");
    }

    /**
     * Get aspect ratio for Imagen API based on size selection.
     */
    private function getSizeDimensions($size)
    {
        $dimensions = [
            'square' => ['aspectRatio' => '1:1'],
            'landscape' => ['aspectRatio' => '16:9'],
            'portrait' => ['aspectRatio' => '9:16'],
            'business-card' => ['aspectRatio' => '1.75:1'], // 3.5 x 2
            'flyer' => ['aspectRatio' => '1:1.414'], // A4
            'poster' => ['aspectRatio' => '2:3'],
        ];
        
        return $dimensions[$size] ?? $dimensions['square'];
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
