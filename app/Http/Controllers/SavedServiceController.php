<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\SavedService;
use App\Models\SavedServiceCollection;
use App\Models\Service;
use App\Models\CustomizationOption;

class SavedServiceController extends Controller
{
    /**
     * Display saved services page
     */
    public function index()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login');
        }
        
        // Get saved services collection
        $savedServices = new SavedServiceCollection($userId);
        $services = $savedServices->itemsWithRelationships();
        
        $subtotal = $savedServices->subtotal;
        $shipping = $subtotal > 0 ? 100 : 0; // Flat shipping fee
        $total = $subtotal + $shipping;
        
        return view('saved-services.index', compact(
            'savedServices', 
            'services', 
            'subtotal', 
            'shipping', 
            'total'
        ));
    }
    
    /**
     * Save a service
     */
    public function save(Request $request)
    {
        $request->validate([
            'service_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1|max:100',
            'customizations' => 'nullable|array',
            'customizations.*' => 'uuid',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $userId = session('user_id');
            
            if (!$userId) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please login to save services'
                    ], 401);
                }

                return redirect()->route('login')->with('error', 'Please login to save services');
            }
            
            // Save service
            $savedService = SavedService::saveService(
                $userId,
                $request->service_id,
                $request->quantity,
                $request->customizations ?? [],
                $request->custom_fields ?? [],
                $request->notes
            );
            
            // Get updated count
            $count = SavedService::getServicesCount($userId);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service saved successfully!',
                    'saved_service_id' => $savedService->saved_service_id,
                    'count' => $count,
                    'total_amount' => SavedService::getTotalAmount($userId)
                ]);
            }

            return redirect()->back()->with('success', 'Service saved successfully!');
            
        } catch (\Throwable $e) {
            Log::error('SavedServiceController@save error: ' . $e->getMessage(), ['exception' => $e]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save service: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to save service. Please try again.');
        }
    }
    
    /**
     * Update saved service quantity
     */
    public function update(Request $request, $savedServiceId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            Log::info('SavedServiceController@update request', [
                'user_id' => $userId,
                'saved_service_id' => $savedServiceId,
                'quantity' => $request->quantity,
                'has_special_instructions' => $request->has('special_instructions'),
            ]);

            $savedService = SavedService::updateServiceQuantity(
                $userId,
                $savedServiceId,
                (int) $request->quantity
            );

            // quantity == 0 means removed
            if ($savedService === null) {
                return response()->json([
                    'success' => true,
                    'removed' => true,
                    'message' => 'Service removed successfully!',
                    'count' => SavedService::getServicesCount($userId),
                    'total_amount' => SavedService::getTotalAmount($userId),
                ]);
            }

            if ($savedService === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            if ($request->has('special_instructions')) {
                $savedService->update([
                    'special_instructions' => $request->special_instructions,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully!',
                'total_price' => $savedService->formatted_total_price,
                'total_amount' => SavedService::getTotalAmount($userId)
            ]);
            
        } catch (\Throwable $e) {
            Log::error('SavedServiceController@update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store selected saved services for checkout
     */
    public function setSelection(Request $request)
    {
        $request->validate([
            'selected' => 'required|array|min:1',
            'selected.*' => 'uuid',
        ]);

        try {
            $userId = session('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $selected = array_values(array_unique($request->selected));

            // Ensure the selected IDs belong to the current user
            $validIds = SavedService::where('user_id', $userId)
                ->whereIn('saved_service_id', $selected)
                ->pluck('saved_service_id')
                ->all();

            if (empty($validIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid saved services selected'
                ], 422);
            }

            session(['checkout_saved_service_ids' => $validIds]);

            return response()->json([
                'success' => true,
                'selected_count' => count($validIds),
            ]);
        } catch (\Throwable $e) {
            Log::error('SavedServiceController@setSelection error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to set selection: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear selected saved services for checkout
     */
    public function clearSelection(Request $request)
    {
        try {
            session()->forget('checkout_saved_service_ids');
            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('SavedServiceController@clearSelection error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear selection: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Remove saved service
     */
    public function remove(Request $request, $savedServiceId)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            $result = SavedService::removeService($userId, $savedServiceId);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service removed successfully!',
                    'count' => SavedService::getServicesCount($userId),
                    'total_amount' => SavedService::getTotalAmount($userId)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }
            
        } catch (\Throwable $e) {
            Log::error('SavedServiceController@remove error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove service: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear all saved services
     */
    public function clear(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            $result = SavedService::clearServices($userId);
            
            return response()->json([
                'success' => true,
                'message' => 'All saved services cleared successfully!',
                'count' => 0,
                'total_amount' => 0
            ]);
            
        } catch (\Throwable $e) {
            Log::error('SavedServiceController@clear error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear services: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get saved services count (for AJAX updates)
     */
    public function getCount(Request $request)
    {
        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'count' => 0,
                    'total_amount' => 0
                ]);
            }
            
            return response()->json([
                'success' => true,
                'count' => SavedService::getServicesCount($userId),
                'total_amount' => SavedService::getTotalAmount($userId)
            ]);
            
        } catch (\Throwable $e) {
            Log::error('SavedServiceController@getCount error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get count: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
