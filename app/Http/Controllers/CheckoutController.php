<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\SavedService;
use App\Models\Service;
use App\Models\CustomizationOption;

class CheckoutController extends Controller
{
    public function fromService(Request $request)
    {
        $request->validate([
            'service_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1|max:100',
            'customizations' => 'nullable|array',
            'customizations.*' => 'uuid',
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'design_files' => 'nullable|array',
            'design_files.*' => 'file|mimes:jpg,jpeg,png,pdf,ai,psd,eps,svg|max:51200',
            'design_notes' => 'nullable|string|max:2000',
        ]);

        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to checkout');
        }

        try {
            $service = Service::with('customFields')->findOrFail($request->service_id);

            $customizations = is_array($request->customizations ?? null) ? array_values(array_unique($request->customizations)) : [];
            sort($customizations);

            $customFields = is_array($request->custom_fields ?? null) ? $request->custom_fields : [];
            $customFields = array_filter($customFields, fn($v) => $v !== null);
            foreach ($customFields as $k => $v) {
                if (!is_string($k)) {
                    unset($customFields[$k]);
                    continue;
                }
                $customFields[$k] = is_string($v) ? trim($v) : '';
                if ($customFields[$k] === '') {
                    unset($customFields[$k]);
                }
            }
            ksort($customFields);

            // Validate required custom fields
            $missing = [];
            foreach ($service->customFields->where('is_required', true) as $field) {
                if (empty($customFields[$field->field_id] ?? null)) {
                    $missing[] = $field->field_label;
                }
            }
            if (!empty($missing)) {
                return redirect()->back()->with('error', 'Please fill in required fields: ' . implode(', ', $missing));
            }

            // Resolve upload flags (backward compatible)
            $requiresFileUpload = false;
            $uploadEnabled = false;
            if (Schema::hasColumn('services', 'requires_file_upload')) {
                $requiresFileUpload = (bool) $service->requires_file_upload;
                if (Schema::hasColumn('services', 'file_upload_enabled')) {
                    $uploadEnabled = (bool) $service->file_upload_enabled;
                } else {
                    $uploadEnabled = $requiresFileUpload;
                }
            }

            if ($requiresFileUpload) {
                $uploadEnabled = true;
            }

            if ($requiresFileUpload && !$request->hasFile('design_files')) {
                return redirect()->back()->with('error', 'This service requires design files. Please upload at least one file to proceed.');
            }

            // Price calculation
            $unitPrice = (float) $service->base_price;
            if (!empty($customizations)) {
                $customizationCost = CustomizationOption::whereIn('option_id', $customizations)->sum('price_modifier');
                $unitPrice += (float) $customizationCost;
            }
            $qty = (int) $request->quantity;
            $totalPrice = $unitPrice * $qty;

            // Handle direct-uploaded files (stored under checkout_direct/{user})
            $directFiles = [];
            if ($uploadEnabled && $request->hasFile('design_files')) {
                foreach ((array) $request->file('design_files') as $file) {
                    if (!$file) {
                        continue;
                    }

                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('design_files/checkout_direct/' . $userId, $fileName, 'public');

                    $directFiles[] = [
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'design_notes' => $request->input('design_notes'),
                    ];
                }
            }

            // Store direct checkout item in session (no saved_services)
            session()->forget(['checkout_saved_service_ids']);
            session([
                'checkout_mode' => 'direct',
                'checkout_direct_item' => [
                    'service_id' => $service->service_id,
                    'quantity' => $qty,
                    'customizations' => $customizations,
                    'custom_fields' => $customFields,
                    'notes' => $request->notes,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'requires_file_upload' => $requiresFileUpload,
                    'file_upload_enabled' => $uploadEnabled,
                ],
                'checkout_direct_design_files' => $directFiles,
            ]);

            return redirect()->route('checkout.index');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Checkout fromService Error', [
                'error' => $e->getMessage(),
                'service_id' => $request->service_id,
                'user_id' => $userId,
            ]);
            return redirect()->back()->with('error', 'Failed to start checkout. Please try again.');
        }
    }

    /**
     * Show checkout page
     */
    public function index()
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to checkout');
        }

        // Direct checkout (single item) takes precedence
        if (session('checkout_mode') === 'direct' && is_array(session('checkout_direct_item'))) {
            $item = session('checkout_direct_item');

            $serviceData = DB::table('services')
                ->join('enterprises', 'services.enterprise_id', '=', 'enterprises.enterprise_id')
                ->where('services.service_id', $item['service_id'])
                ->select('services.*', 'enterprises.name as enterprise_name', 'enterprises.enterprise_id')
                ->first();

            if (!$serviceData) {
                session()->forget(['checkout_mode', 'checkout_direct_item', 'checkout_direct_design_files']);
                return redirect()->route('customer.orders')->with('error', 'Service not found.');
            }

            $customizations = [];
            if (!empty($item['customizations'])) {
                foreach ($item['customizations'] as $optionId) {
                    $option = DB::table('customization_options')->where('option_id', $optionId)->first();
                    if ($option) {
                        $customizations[] = $option;
                    }
                }
            }

            $cartItems = [[
                'key' => 'direct',
                'service' => $serviceData,
                'service_id' => $item['service_id'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'total' => (float) $item['total_price'],
                'customizations' => $customizations,
                'special_instructions' => $item['notes'] ?? null,
            ]];

            $subtotal = (float) $item['total_price'];
            $tax = $subtotal * 0.12;
            $total = $subtotal + $tax;

            $user = DB::table('users')->where('user_id', $userId)->first();

            $availablePaymentMethods = ['gcash', 'cash'];
            $availableFulfillmentMethods = ['pickup', 'delivery'];

            // filter allowed methods using the single service
            if (Schema::hasColumn('services', 'allowed_payment_methods') && !empty($serviceData->allowed_payment_methods)) {
                $decoded = json_decode($serviceData->allowed_payment_methods, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $availablePaymentMethods = array_values(array_intersect($availablePaymentMethods, $decoded));
                }
            }

            if (Schema::hasColumn('services', 'fulfillment_type') && !empty($serviceData->fulfillment_type)) {
                $supported = $serviceData->fulfillment_type === 'pickup'
                    ? ['pickup']
                    : ($serviceData->fulfillment_type === 'delivery' ? ['delivery'] : ['pickup', 'delivery']);

                $availableFulfillmentMethods = array_values(array_intersect($availableFulfillmentMethods, $supported));
            }

            return view('checkout.index', compact('cartItems', 'subtotal', 'tax', 'total', 'user', 'availablePaymentMethods', 'availableFulfillmentMethods'));
        }

        // Fallback: saved services checkout (existing behavior)
        $savedServices = SavedService::getUserServices($userId);

        $selectedIds = session('checkout_saved_service_ids');
        if (is_array($selectedIds) && !empty($selectedIds)) {
            $savedServices = $savedServices->whereIn('saved_service_id', $selectedIds)->values();
        }
        
        if ($savedServices->isEmpty()) {
            return redirect()->route('saved-services.index')->with('error', 'Your saved services are empty');
        }

        // Get saved services with full details
        $cartItems = [];
        $subtotal = 0;
        
        foreach ($savedServices as $savedService) {
            $serviceData = DB::table('services')
                ->join('enterprises', 'services.enterprise_id', '=', 'enterprises.enterprise_id')
                ->where('services.service_id', $savedService->service_id)
                ->select('services.*', 'enterprises.name as enterprise_name', 'enterprises.enterprise_id')
                ->first();
            
            if (!$serviceData) continue;
            
            $itemPrice = $savedService->unit_price;
            $itemTotal = $savedService->total_price;
            $subtotal += $itemTotal;
            
            // Get customizations
            $customizations = [];
            if ($savedService->customizations) {
                // Check if customizations is already an array (due to model casting)
                $customizationIds = is_array($savedService->customizations) 
                    ? $savedService->customizations 
                    : json_decode($savedService->customizations, true);
                    
                if (is_array($customizationIds)) {
                    foreach ($customizationIds as $optionId) {
                        $option = DB::table('customization_options')
                            ->where('option_id', $optionId)
                            ->first();
                        if ($option) {
                            $customizations[] = $option;
                        }
                    }
                }
            }
            
            $cartItems[] = [
                'key' => $savedService->saved_service_id,
                'service' => $serviceData,
                'service_id' => $savedService->service_id,
                'quantity' => $savedService->quantity,
                'unit_price' => $itemPrice,
                'total' => $itemTotal,
                'customizations' => $customizations,
                'special_instructions' => $savedService->special_instructions,
            ];
        }
        
        // Get user information
        $user = DB::table('users')->where('user_id', $userId)->first();
        
        // Calculate totals (no shipping for pickup)
        $tax = $subtotal * 0.12; // 12% tax
        $total = $subtotal + $tax;

        $availablePaymentMethods = ['gcash', 'cash'];
        $availableFulfillmentMethods = ['pickup', 'delivery'];

        foreach ($cartItems as $item) {
            $service = $item['service'] ?? null;
            if (!$service) continue;

            if (Schema::hasColumn('services', 'allowed_payment_methods') && !empty($service->allowed_payment_methods)) {
                $decoded = json_decode($service->allowed_payment_methods, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $availablePaymentMethods = array_values(array_intersect($availablePaymentMethods, $decoded));
                }
            }

            if (Schema::hasColumn('services', 'fulfillment_type') && !empty($service->fulfillment_type)) {
                $supported = [];
                if ($service->fulfillment_type === 'pickup') {
                    $supported = ['pickup'];
                } elseif ($service->fulfillment_type === 'delivery') {
                    $supported = ['delivery'];
                } else {
                    $supported = ['pickup', 'delivery'];
                }
                $availableFulfillmentMethods = array_values(array_intersect($availableFulfillmentMethods, $supported));
            }
        }

        return view('checkout.index', compact('cartItems', 'subtotal', 'tax', 'total', 'user', 'availablePaymentMethods', 'availableFulfillmentMethods'));
    }

    /**
     * Process checkout
     */
    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:gcash,cash',
            'fulfillment_method' => 'required|in:pickup,delivery',
            'requested_fulfillment_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'contact_name' => 'required|string|max:255',
            'rush_option' => 'required|in:standard,express,rush,same_day',
            'rush_fee' => 'required|numeric|min:0'
        ]);

        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Please login to checkout'], 401);
        }

        $direct = session('checkout_mode') === 'direct' && is_array(session('checkout_direct_item'));
        $selectedIds = null;

        if ($direct) {
            $item = session('checkout_direct_item');
            $savedServices = collect([(object) [
                'saved_service_id' => null,
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
                'customizations' => $item['customizations'] ?? [],
                'custom_fields' => $item['custom_fields'] ?? [],
                'special_instructions' => $item['notes'] ?? null,
            ]]);
        } else {
            // Get saved services instead of cart
            $savedServices = SavedService::getUserServices($userId);

            $selectedIds = session('checkout_saved_service_ids');
            if (is_array($selectedIds) && !empty($selectedIds)) {
                $savedServices = $savedServices->whereIn('saved_service_id', $selectedIds)->values();
            }
            
            if ($savedServices->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No saved services found'], 400);
            }
        }

        DB::beginTransaction();
        
        try {
            // Process saved services or direct item and create order(s)
            $ordersByEnterprise = [];
            
            foreach ($savedServices as $savedService) {
                $serviceData = DB::table('services')->where('service_id', $savedService->service_id)->first();
                if (!$serviceData) continue;
                
                if (!isset($ordersByEnterprise[$serviceData->enterprise_id])) {
                    $ordersByEnterprise[$serviceData->enterprise_id] = [];
                }
                $ordersByEnterprise[$serviceData->enterprise_id][] = $savedService;
            }
            
            $createdOrders = [];
            $pendingStatus = DB::table('statuses')->where('status_name', 'Pending')->first();
            
            // Create one order per enterprise
            foreach ($ordersByEnterprise as $enterpriseId => $items) {
                $orderId = Str::uuid();
                $orderNo = 'ORD-' . date('Y') . '-' . strtoupper(Str::random(8));
                
                $subtotal = 0;
                $orderItems = [];

                $savedServiceIds = [];
                foreach ($items as $savedService) {
                    if (!empty($savedService->saved_service_id)) {
                        $savedServiceIds[] = $savedService->saved_service_id;
                    }
                }

                $tempDesignFiles = collect();
                if (!$direct && Schema::hasTable('saved_service_design_files') && !empty($savedServiceIds)) {
                    $tempDesignFiles = DB::table('saved_service_design_files')
                        ->where('user_id', $userId)
                        ->whereIn('saved_service_id', $savedServiceIds)
                        ->orderBy('created_at')
                        ->get();
                }
                
                foreach ($items as $savedService) {
                    $serviceData = DB::table('services')->where('service_id', $savedService->service_id)->first();

                    if (Schema::hasColumn('services', 'requires_file_upload') && !empty($serviceData?->requires_file_upload)) {
                        if ($direct) {
                            $directFiles = collect(session('checkout_direct_design_files', []));
                            if ($directFiles->count() === 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'This service requires design files. Please upload the required files before checkout.'
                                ], 422);
                            }
                        } else {
                            if (Schema::hasTable('saved_service_design_files') && $tempDesignFiles->where('saved_service_id', $savedService->saved_service_id)->count() === 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'One or more services require design files. Please upload the required files before checkout.'
                                ], 422);
                            }
                        }
                    }

                    $itemPrice = $savedService->unit_price;
                    $itemTotal = $savedService->total_price;

                    if ($request->fulfillment_method && Schema::hasColumn('services', 'fulfillment_type') && !empty($serviceData->fulfillment_type)) {
                        if ($serviceData->fulfillment_type === 'pickup' && $request->fulfillment_method !== 'pickup') {
                            return response()->json(['success' => false, 'message' => 'One or more services are pickup-only.'], 422);
                        }
                        if ($serviceData->fulfillment_type === 'delivery' && $request->fulfillment_method !== 'delivery') {
                            return response()->json(['success' => false, 'message' => 'One or more services are delivery-only.'], 422);
                        }
                    }

                    if ($request->payment_method && Schema::hasColumn('services', 'allowed_payment_methods') && !empty($serviceData->allowed_payment_methods)) {
                        $decoded = json_decode($serviceData->allowed_payment_methods, true);
                        if (is_array($decoded) && !empty($decoded) && !in_array($request->payment_method, $decoded, true)) {
                            return response()->json(['success' => false, 'message' => 'Selected payment method is not accepted by one or more services.'], 422);
                        }
                    }
                    
                    // Get customizations
                    $customizationsData = [];
                    if ($savedService->customizations) {
                        // Check if customizations is already an array (due to model casting)
                        $customizationIds = is_array($savedService->customizations) 
                            ? $savedService->customizations 
                            : json_decode($savedService->customizations, true);
                            
                        if (is_array($customizationIds)) {
                            foreach ($customizationIds as $optionId) {
                                $option = DB::table('customization_options')->where('option_id', $optionId)->first();
                                if ($option) {
                                    $customizationsData[] = $option;
                                }
                            }
                        }
                    }
                    
                    $subtotal += $itemTotal;

                    $itemInsert = [
                        'item_id' => Str::uuid(),
                        'purchase_order_id' => $orderId,
                        'service_id' => $savedService->service_id,
                        'item_description' => $serviceData->service_name,
                        'quantity' => $savedService->quantity,
                        'unit_price' => $itemPrice,
                        'total_cost' => $itemTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('order_items', 'custom_fields')) {
                        $customFieldsValue = $savedService->custom_fields ?? null;
                        if (is_array($customFieldsValue)) {
                            $customFieldsValue = empty($customFieldsValue) ? null : json_encode($customFieldsValue);
                        }
                        $itemInsert['custom_fields'] = $customFieldsValue;
                    }
                    
                    $orderItems[] = [
                        'item' => $itemInsert,
                        'customizations' => $customizationsData,
                    ];
                }
                
                // Calculate rush delivery details
                $rushFee = (float) $request->rush_fee;
                $rushOption = $request->rush_option;
                
                // Calculate pickup date based on rush option
                $pickupDate = $this->calculatePickupDate($rushOption);
                $deliveryDate = $pickupDate ? $pickupDate->toDateString() : now()->toDateString();

                $requestedDate = $request->requested_fulfillment_date;
                if ($requestedDate) {
                    if ($requestedDate < $deliveryDate) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Your requested date is too soon for the selected timeline. Please choose a later date.'
                        ], 422);
                    }
                    $deliveryDate = $requestedDate;
                }
                
                $tax = ($subtotal + $rushFee) * 0.12;
                $total = $subtotal + $rushFee + $tax;
                
                // Create order
                $orderData = [
                    'purchase_order_id' => $orderId,
                    'customer_id' => $userId,
                    'enterprise_id' => $enterpriseId,
                    'status_id' => $pendingStatus?->status_id,
                    'order_no' => $orderNo,
                    'purpose' => $request->notes ?? 'Online order via UniPrint',
                    'date_requested' => now()->toDateString(),
                    'delivery_date' => $deliveryDate,
                    'pickup_date' => $pickupDate,
                    'shipping_fee' => 0, // No shipping for pickup
                    'rush_fee' => $rushFee,
                    'rush_option' => $rushOption,
                    'discount' => 0,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'contact_name' => $request->contact_name,
                    'contact_phone' => $request->contact_phone,
                    'contact_email' => $request->contact_email,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('customer_orders', 'fulfillment_method')) {
                    $orderData['fulfillment_method'] = $request->fulfillment_method;
                }

                if (Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
                    $orderData['requested_fulfillment_date'] = $request->requested_fulfillment_date;
                }
                
                DB::table('customer_orders')->insert($orderData);
                
                // Create order items
                foreach ($orderItems as $orderItem) {
                    DB::table('order_items')->insert($orderItem['item']);
                    
                    // Create customizations
                    foreach ($orderItem['customizations'] as $custom) {
                        DB::table('order_item_customizations')->insert([
                            'id' => Str::uuid(),
                            'order_item_id' => $orderItem['item']['item_id'],
                            'option_id' => $custom->option_id,
                            'price_snapshot' => $custom->price_modifier,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                
                // Create initial status
                DB::table('order_status_history')->insert([
                    'approval_id' => Str::uuid(),
                    'purchase_order_id' => $orderId,
                    'user_id' => $userId,
                    'status_id' => $pendingStatus->status_id,
                    'remarks' => 'Order placed via checkout',
                    'timestamp' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Create payment record
                try {
                    if (Schema::hasTable('payments')) {
                        DB::table('payments')->insert([
                            'payment_id' => Str::uuid(),
                            'purchase_order_id' => $orderId,
                            'payment_method' => $request->payment_method,
                            'amount_paid' => 0,
                            'amount_due' => $total,
                            'payment_date_time' => now(),
                            'is_verified' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        Log::warning('payments table missing; skipping payment insert during checkout', [
                            'purchase_order_id' => $orderId,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to insert payment record; continuing checkout', [
                        'purchase_order_id' => $orderId,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Attach uploaded design files (from saved service temp uploads) to the created order
                if (Schema::hasTable('order_design_files')) {
                    if ($direct) {
                        $directFiles = collect(session('checkout_direct_design_files', []));
                        $version = DB::table('order_design_files')
                            ->where('purchase_order_id', $orderId)
                            ->count();

                        foreach ($directFiles as $tmp) {
                            $version++;
                            $targetName = $version . '_' . basename($tmp['file_name']);
                            $targetPath = 'design_files/' . $orderId . '/' . $targetName;

                            if (Storage::disk('public')->exists($tmp['file_path'])) {
                                Storage::disk('public')->makeDirectory('design_files/' . $orderId);
                                Storage::disk('public')->move($tmp['file_path'], $targetPath);
                            }

                            DB::table('order_design_files')->insert([
                                'file_id' => Str::uuid(),
                                'purchase_order_id' => $orderId,
                                'uploaded_by' => $userId,
                                'file_name' => $targetName,
                                'file_path' => $targetPath,
                                'file_type' => $tmp['file_type'],
                                'file_size' => $tmp['file_size'],
                                'design_notes' => $tmp['design_notes'] ?? null,
                                'version' => $version,
                                'is_approved' => false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    } elseif (Schema::hasTable('saved_service_design_files')) {
                        $version = DB::table('order_design_files')
                            ->where('purchase_order_id', $orderId)
                            ->count();

                        foreach ($tempDesignFiles as $tmp) {
                            $version++;
                            $targetName = $version . '_' . basename($tmp->file_name);
                            $targetPath = 'design_files/' . $orderId . '/' . $targetName;

                            if (Storage::disk('public')->exists($tmp->file_path)) {
                                Storage::disk('public')->makeDirectory('design_files/' . $orderId);
                                Storage::disk('public')->move($tmp->file_path, $targetPath);
                            }

                            DB::table('order_design_files')->insert([
                                'file_id' => Str::uuid(),
                                'purchase_order_id' => $orderId,
                                'uploaded_by' => $userId,
                                'file_name' => $targetName,
                                'file_path' => $targetPath,
                                'file_type' => $tmp->file_type,
                                'file_size' => $tmp->file_size,
                                'design_notes' => $tmp->design_notes,
                                'version' => $version,
                                'is_approved' => false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        DB::table('saved_service_design_files')
                            ->where('user_id', $userId)
                            ->whereIn('saved_service_id', $savedServiceIds)
                            ->delete();
                    }
                }
                
                $createdOrders[] = [
                    'order_no' => $orderNo,
                    'total' => $total,
                    'payment_method' => $request->payment_method
                ];
            }
            
            DB::commit();
            
            if ($direct) {
                session()->forget(['checkout_mode', 'checkout_direct_item', 'checkout_direct_design_files']);
            } else {
                // Clear saved services after successful order
                if (is_array($selectedIds) && !empty($selectedIds)) {
                    SavedService::where('user_id', $userId)
                        ->whereIn('saved_service_id', $selectedIds)
                        ->delete();
                    session()->forget('checkout_saved_service_ids');
                } else {
                    SavedService::clearServices($userId);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'orders' => $createdOrders
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Process Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate pickup date based on rush option
     */
    private function calculatePickupDate($rushOption)
    {
        $now = now();
        
        switch ($rushOption) {
            case 'same_day':
                // Same day: add 2-3 hours
                return $now->addHours(3);
                
            case 'rush':
                // Rush: add 4-6 hours
                return $now->addHours(6);
                
            case 'express':
                // Express: next business day by 5 PM
                $pickupDate = $now->addDay();
                $pickupDate->setTime(17, 0, 0); // 5:00 PM
                
                // Skip weekends
                while ($pickupDate->isWeekend()) {
                    $pickupDate->addDay();
                }
                
                return $pickupDate;
                
            case 'standard':
            default:
                // Standard: 2-3 business days by 5 PM
                $pickupDate = $now->copy();
                $businessDays = 0;
                
                while ($businessDays < 2) {
                    $pickupDate->addDay();
                    if (!$pickupDate->isWeekend()) {
                        $businessDays++;
                    }
                }
                
                $pickupDate->setTime(17, 0, 0); // 5:00 PM
                return $pickupDate;
        }
    }

    /**
     * Apply discount code
     */
    public function applyDiscountCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0'
        ]);

        try {
            $code = strtoupper($request->code);
            $subtotal = $request->subtotal;
            
            // Check if discount code exists and is valid
            $discount = DB::table('discount_codes')
                ->where('code', $code)
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->first();
            
            if (!$discount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired discount code'
                ]);
            }
            
            // Calculate discount amount
            $discountAmount = 0;
            if ($discount->discount_type === 'percentage') {
                $discountAmount = $subtotal * ($discount->discount_value / 100);
            } else {
                $discountAmount = min($discount->discount_value, $subtotal);
            }
            
            // Check minimum order requirement
            if ($discount->minimum_order > 0 && $subtotal < $discount->minimum_order) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum order of ₱{$discount->minimum_order} required for this discount"
                ]);
            }
            
            return response()->json([
                'success' => true,
                'discount_amount' => $discountAmount,
                'discount_type' => $discount->discount_type,
                'discount_value' => $discount->discount_value,
                'message' => "Discount applied! You saved ₱{$discountAmount}"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Discount Code Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply discount code'
            ], 500);
        }
    }
}
