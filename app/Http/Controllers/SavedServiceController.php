<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            
        } catch (\Exception $e) {
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
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        try {
            $userId = session('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            $savedService = SavedService::updateServiceQuantity(
                $userId,
                $savedServiceId,
                $request->quantity
            );
            
            if ($savedService) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service updated successfully!',
                    'total_price' => $savedService->formatted_total_price,
                    'total_amount' => SavedService::getTotalAmount($userId)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update service: ' . $e->getMessage()
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
            
        } catch (\Exception $e) {
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
            
        } catch (\Exception $e) {
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
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get count: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
