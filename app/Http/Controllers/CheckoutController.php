<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutFromServiceRequest;
use App\Http\Requests\CheckoutProcessRequest;
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
use App\Services\OrderProcessingService;
use App\Services\PricingEngine;

class CheckoutController extends Controller
{
    private function paypalBaseUrl(): string
    {
        $mode = (string) config('services.paypal.mode', 'sandbox');
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function paypalCurrency(): string
    {
        return (string) config('services.paypal.currency', 'PHP');
    }

    private function paypalAccessToken(): string
    {
        $clientId = (string) config('services.paypal.client_id');
        $secret = (string) config('services.paypal.client_secret');

        if ($clientId === '' || $secret === '') {
            throw new \RuntimeException('PayPal is not configured');
        }

        $res = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->post($this->paypalBaseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$res->ok()) {
            throw new \RuntimeException('Failed to authenticate with PayPal');
        }

        $token = (string) ($res->json('access_token') ?? '');
        if ($token === '') {
            throw new \RuntimeException('PayPal access token missing');
        }

        return $token;
    }

    private function computeCheckoutTotals(string $userId, string $rushOption): array
    {
        $direct = session('checkout_mode') === 'direct' && is_array(session('checkout_direct_item'));

        if ($direct) {
            $item = session('checkout_direct_item');
            $subtotal = (float) ($item['total_price'] ?? 0);
        } else {
            $savedServices = SavedService::getUserServices($userId);
            $selectedIds = session('checkout_saved_service_ids');
            if (is_array($selectedIds) && !empty($selectedIds)) {
                $savedServices = $savedServices->whereIn('saved_service_id', $selectedIds)->values();
            }

            if ($savedServices->isEmpty()) {
                $subtotal = 0.0;
            } else {
                $subtotal = (float) $savedServices->sum('total_price');
            }
        }

        $pricing = app(PricingEngine::class);
        $rushFee = (float) $pricing->calculateRushFee($rushOption);
        $tax = (float) $pricing->calculateTax($subtotal + $rushFee);
        $total = $subtotal + $rushFee + $tax;

        return [
            'subtotal' => $subtotal,
            'rush_fee' => $rushFee,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    public function fromService(CheckoutFromServiceRequest $request)
    {
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

            // Validate custom size inputs when Custom Size is selected
            if (schema_has_column('services', 'supports_custom_size') && !empty($service->supports_custom_size)) {
                $customSizeOptionId = DB::table('customization_options')
                    ->where('service_id', $service->service_id)
                    ->whereRaw('LOWER(option_type) = ?', ['size'])
                    ->whereRaw('LOWER(option_name) = ?', ['custom size'])
                    ->value('option_id');

                $customSizeSelected = $customSizeOptionId && in_array((string) $customSizeOptionId, $customizations, true);

                if ($customSizeSelected) {
                    $wRaw = $customFields['custom_size_width'] ?? null;
                    $hRaw = $customFields['custom_size_height'] ?? null;

                    if ($wRaw === null || $hRaw === null) {
                        return redirect()->back()->with('error', 'Please enter both custom width and height.');
                    }

                    $w = (float) $wRaw;
                    $h = (float) $hRaw;

                    if ($w <= 0 || $h <= 0) {
                        return redirect()->back()->with('error', 'Custom width and height must be greater than 0.');
                    }

                    $minW = schema_has_column('services', 'custom_size_min_width') ? (float) ($service->custom_size_min_width ?? 0) : 0;
                    $maxW = schema_has_column('services', 'custom_size_max_width') ? (float) ($service->custom_size_max_width ?? 0) : 0;
                    $minH = schema_has_column('services', 'custom_size_min_height') ? (float) ($service->custom_size_min_height ?? 0) : 0;
                    $maxH = schema_has_column('services', 'custom_size_max_height') ? (float) ($service->custom_size_max_height ?? 0) : 0;

                    if (($minW > 0 && $w < $minW) || ($maxW > 0 && $w > $maxW) || ($minH > 0 && $h < $minH) || ($maxH > 0 && $h > $maxH)) {
                        return redirect()->back()->with('error', 'Custom size is out of the allowed range.');
                    }
                } else {
                    unset($customFields['custom_size_width'], $customFields['custom_size_height']);
                }
            }

            // Resolve upload flags (backward compatible)
            $requiresFileUpload = false;
            $uploadEnabled = false;
            if (schema_has_column('services', 'requires_file_upload')) {
                $requiresFileUpload = (bool) $service->requires_file_upload;
                if (schema_has_column('services', 'file_upload_enabled')) {
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
                $disk = config('filesystems.default', 'public');
                foreach ((array) $request->file('design_files') as $file) {
                    if (!$file) {
                        continue;
                    }

                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('design_files/checkout_direct/' . $userId, $fileName, $disk);

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
                ->select('services.*', 'enterprises.name as enterprise_name', 'enterprises.enterprise_id', 'enterprises.checkout_payment_methods', 'enterprises.checkout_fulfillment_methods', 'enterprises.checkout_rush_options')
                ->first();

            if (!$serviceData) {
                session()->forget(['checkout_mode', 'checkout_direct_item', 'checkout_direct_design_files']);
                return redirect()->route('customer.orders')->with('error', 'Service not found.');
            }

            $customizations = [];
            if (!empty($item['customizations']) && is_array($item['customizations'])) {
                $optionIds = collect($item['customizations'])->filter()->values()->all();
                if (!empty($optionIds)) {
                    $customizations = DB::table('customization_options')
                        ->whereIn('option_id', $optionIds)
                        ->get()
                        ->values()
                        ->all();
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
            $tax = app(\App\Services\PricingEngine::class)->calculateTax($subtotal);
            $total = $subtotal + $tax;

            $user = DB::table('users')->where('user_id', $userId)->first();

            $availablePaymentMethods = ['gcash', 'cash'];
            if ((string) config('services.paypal.client_id') !== '') {
                $availablePaymentMethods[] = 'paypal';
            }
            $availableFulfillmentMethods = ['pickup', 'delivery'];

            // Load business-defined rush options
            $rushOptionsData = [
                'standard' => ['enabled' => true, 'fee' => 0, 'lead_hours' => 48],
                'express' => ['enabled' => false, 'fee' => 50, 'lead_hours' => 24],
                'rush' => ['enabled' => false, 'fee' => 100, 'lead_hours' => 6],
                'same_day' => ['enabled' => false, 'fee' => 200, 'lead_hours' => 3],
            ];
            if (!empty($serviceData->checkout_rush_options)) {
                $decodedRush = is_array($serviceData->checkout_rush_options) 
                    ? $serviceData->checkout_rush_options 
                    : json_decode($serviceData->checkout_rush_options, true);
                if (is_array($decodedRush)) {
                    foreach ($rushOptionsData as $k => $v) {
                        if (isset($decodedRush[$k])) {
                            $rushOptionsData[$k] = array_merge($v, $decodedRush[$k]);
                        }
                    }
                }
            }

            $supportsRushAll = (bool) ($serviceData->supports_rush ?? false);

            $requiresDownpayment = false;
            if (schema_has_column('services', 'requires_downpayment')) {
                $requiresDownpayment = !empty($serviceData->requires_downpayment);
            }

            // filter allowed methods using the single service
            if (!empty($serviceData->allowed_payment_methods)) {
                $decoded = is_array($serviceData->allowed_payment_methods) 
                    ? $serviceData->allowed_payment_methods 
                    : json_decode($serviceData->allowed_payment_methods, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $availablePaymentMethods = array_values(array_intersect($availablePaymentMethods, $decoded));
                }
            }

            // Downpayment requires GCash (cash is not allowed)
            if ($requiresDownpayment) {
                $availablePaymentMethods = array_values(array_diff($availablePaymentMethods, ['cash']));
            }

            if (schema_has_column('services', 'fulfillment_type') && !empty($serviceData->fulfillment_type)) {
                $supported = $serviceData->fulfillment_type === 'pickup'
                    ? ['pickup']
                    : ($serviceData->fulfillment_type === 'delivery' ? ['delivery'] : ['pickup', 'delivery']);

                $availableFulfillmentMethods = array_values(array_intersect($availableFulfillmentMethods, $supported));
            }

            // Cash only for pickup
            if (!in_array('pickup', $availableFulfillmentMethods, true)) {
                $availablePaymentMethods = array_values(array_diff($availablePaymentMethods, ['cash']));
            }

            return view('checkout.index', compact('cartItems', 'subtotal', 'tax', 'total', 'user', 'availablePaymentMethods', 'availableFulfillmentMethods', 'supportsRushAll', 'rushOptionsData'));
        }

        // Cart-based checkout (multiple items)
        $savedServices = \App\Models\SavedService::getUserServices($userId);
        $selectedIds = session('checkout_saved_service_ids');
        if (is_array($selectedIds) && !empty($selectedIds)) {
            $savedServices = $savedServices->whereIn('saved_service_id', $selectedIds)->values();
        }

        if ($savedServices->isEmpty()) {
            return redirect()->route('customer.orders')->with('error', 'No items selected for checkout.');
        }

        $serviceIds = $savedServices->pluck('service_id')->unique()->all();
        $services = \App\Models\Service::whereIn('service_id', $serviceIds)->get()->keyBy('service_id');

        $cartItems = [];
        $subtotal = 0;
        $supportsRushAll = true;
        $availablePaymentMethods = ['gcash', 'cash'];
        if ((string) config('services.paypal.client_id') !== '') {
            $availablePaymentMethods[] = 'paypal';
        }
        $availableFulfillmentMethods = ['pickup', 'delivery'];
        $requiresDownpayment = false;

        $allCustomizationIds = $savedServices
            ->flatMap(function ($savedService) {
                $raw = $savedService->customizations ?? null;
                $ids = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
                return is_array($ids) ? $ids : [];
            })
            ->filter()
            ->unique()
            ->values();

        $customizationOptionsById = collect();
        if ($allCustomizationIds->isNotEmpty()) {
            $customizationOptionsById = DB::table('customization_options')
                ->whereIn('option_id', $allCustomizationIds)
                ->get()
                ->keyBy('option_id');
        }

        $rushOptionsData = [
            'standard' => ['enabled' => true, 'fee' => 0, 'lead_hours' => 48],
            'express' => ['enabled' => false, 'fee' => 50, 'lead_hours' => 24],
            'rush' => ['enabled' => false, 'fee' => 100, 'lead_hours' => 6],
            'same_day' => ['enabled' => false, 'fee' => 200, 'lead_hours' => 3],
        ];

        foreach ($savedServices as $item) {
            $service = $services->get($item->service_id);
            if (!$service) continue;

            // Load business settings for first service found (assuming same enterprise for all cart items)
            // If multiple enterprises, we merge or take the strictest. For now, we take from first.
            if ($service->enterprise) {
                $ent = $service->enterprise;
                if (!empty($ent->checkout_rush_options)) {
                    $decodedRush = is_array($ent->checkout_rush_options) 
                        ? $ent->checkout_rush_options 
                        : json_decode($ent->checkout_rush_options, true);
                    if (is_array($decodedRush)) {
                        foreach ($rushOptionsData as $k => $v) {
                            if (isset($decodedRush[$k])) {
                                $rushOptionsData[$k] = array_merge($v, $decodedRush[$k]);
                            }
                        }
                    }
                }
            }

            $itemSubtotal = (float) $item->total_price;
            $subtotal += $itemSubtotal;

            if (!$service->supports_rush) {
                $supportsRushAll = false;
            }

            if ($service->requires_downpayment) {
                $requiresDownpayment = true;
            }

            // Intersection of payment methods
            if (!empty($service->allowed_payment_methods)) {
                $decoded = is_array($service->allowed_payment_methods) 
                    ? $service->allowed_payment_methods 
                    : json_decode($service->allowed_payment_methods, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $availablePaymentMethods = array_values(array_intersect($availablePaymentMethods, $decoded));
                }
            }

            // Fulfillment intersection
            if ($service->fulfillment_type) {
                $supported = $service->fulfillment_type === 'pickup'
                    ? ['pickup']
                    : ($service->fulfillment_type === 'delivery' ? ['delivery'] : ['pickup', 'delivery']);
                $availableFulfillmentMethods = array_values(array_intersect($availableFulfillmentMethods, $supported));
            }

            $customizations = [];
            $raw = $item->customizations ?? null;
            $customizationIds = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
            if (is_array($customizationIds)) {
                foreach ($customizationIds as $optionId) {
                    $opt = $customizationOptionsById->get($optionId);
                    if ($opt) {
                        $customizations[] = $opt;
                    }
                }
            }

            $cartItems[] = [
                'key' => $item->saved_service_id,
                'service' => $service,
                'service_id' => $item->service_id,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total' => $itemSubtotal,
                'customizations' => $customizations,
                'special_instructions' => $item->notes ?? null,
            ];
        }

        if ($requiresDownpayment) {
            $availablePaymentMethods = array_values(array_diff($availablePaymentMethods, ['cash']));
        }
        if (!in_array('pickup', $availableFulfillmentMethods, true)) {
            $availablePaymentMethods = array_values(array_diff($availablePaymentMethods, ['cash']));
        }

        $tax = app(\App\Services\PricingEngine::class)->calculateTax($subtotal);
        $total = $subtotal + $tax;
        $user = DB::table('users')->where('user_id', $userId)->first();

        return view('checkout.index', compact('cartItems', 'subtotal', 'tax', 'total', 'user', 'availablePaymentMethods', 'availableFulfillmentMethods', 'supportsRushAll', 'rushOptionsData'));
    }

    /**
     * Process checkout
     */
    public function process(CheckoutProcessRequest $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Please login to checkout'], 401);
        }

        if ($request->payment_method === 'cash' && $request->fulfillment_method !== 'pickup') {
            return response()->json(['success' => false, 'message' => 'Cash is only available for pickup orders.'], 422);
        }

        if ($request->payment_method === 'paypal') {
            $paypalOrderId = (string) $request->input('paypal_order_id', '');
            $captured = (string) session('paypal_captured_order_id');
            if ($paypalOrderId === '' || $captured === '' || $paypalOrderId !== $captured) {
                return response()->json([
                    'success' => false,
                    'message' => 'PayPal payment was not confirmed. Please try again.',
                ], 422);
            }
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

        $tempDesignFiles = collect();
        if (!$direct && schema_has_table('saved_service_design_files')) {
            $savedServiceIds = $savedServices
                ->pluck('saved_service_id')
                ->filter(fn ($v) => !empty($v))
                ->values()
                ->all();

            if (!empty($savedServiceIds)) {
                $tempDesignFiles = DB::table('saved_service_design_files')
                    ->where('user_id', $userId)
                    ->whereIn('saved_service_id', $savedServiceIds)
                    ->orderBy('created_at')
                    ->get();
            }
        }

        $service = app(OrderProcessingService::class);
        $result = $service->process($userId, [
            'payment_method' => $request->payment_method,
            'fulfillment_method' => $request->fulfillment_method,
            'requested_fulfillment_date' => $request->requested_fulfillment_date,
            'notes' => $request->notes,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'contact_name' => $request->contact_name,
            'rush_option' => $request->rush_option,
            'direct' => $direct,
            'selected_ids' => $selectedIds,
            'saved_services' => $savedServices,
            'temp_design_files' => $tempDesignFiles,
            'direct_design_files' => session('checkout_direct_design_files', []),
        ]);

        if (!($result['success'] ?? false)) {
            $status = (int) ($result['status'] ?? 500);
            return response()->json([
                'success' => false,
                'message' => (string) ($result['message'] ?? 'Checkout failed'),
            ], $status);
        }

        if ($direct) {
            session()->forget(['checkout_mode', 'checkout_direct_item', 'checkout_direct_design_files']);
        } else {
            if (is_array($selectedIds) && !empty($selectedIds)) {
                session()->forget('checkout_saved_service_ids');
            }
        }

        return response()->json([
            'success' => true,
            'message' => (string) ($result['message'] ?? 'Order placed successfully!'),
            'orders' => $result['orders'] ?? [],
        ]);
    }

    public function paypalCreateOrder(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if ((string) config('services.paypal.client_id') === '') {
            return response()->json(['success' => false, 'message' => 'PayPal is not configured'], 500);
        }

        $request->validate([
            'rush_option' => 'required|in:standard,express,rush,same_day',
        ]);

        try {
            $totals = $this->computeCheckoutTotals($userId, (string) $request->input('rush_option'));
            $amount = number_format((float) ($totals['total'] ?? 0), 2, '.', '');
            if (((float) $amount) <= 0) {
                return response()->json(['success' => false, 'message' => 'Invalid amount'], 422);
            }

            $token = $this->paypalAccessToken();
            $res = Http::withToken($token)
                ->post($this->paypalBaseUrl() . '/v2/checkout/orders', [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'amount' => [
                                'currency_code' => $this->paypalCurrency(),
                                'value' => $amount,
                            ],
                        ],
                    ],
                ]);

            if (!$res->ok()) {
                Log::error('PayPal create order failed', ['body' => $res->body()]);
                return response()->json(['success' => false, 'message' => 'Failed to create PayPal order'], 500);
            }

            return response()->json([
                'success' => true,
                'id' => $res->json('id'),
            ]);
        } catch (\Throwable $e) {
            Log::error('PayPal create order exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create PayPal order'], 500);
        }
    }

    public function paypalCaptureOrder(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'order_id' => 'required|string|max:255',
        ]);

        try {
            $orderId = (string) $request->input('order_id');
            $token = $this->paypalAccessToken();

            $res = Http::withToken($token)
                ->post($this->paypalBaseUrl() . '/v2/checkout/orders/' . $orderId . '/capture');

            if (!$res->ok()) {
                Log::error('PayPal capture failed', ['body' => $res->body()]);
                return response()->json(['success' => false, 'message' => 'Failed to capture PayPal order'], 500);
            }

            $status = (string) ($res->json('status') ?? '');
            if ($status !== 'COMPLETED') {
                return response()->json(['success' => false, 'message' => 'PayPal payment not completed'], 422);
            }

            session(['paypal_captured_order_id' => $orderId]);

            return response()->json([
                'success' => true,
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            Log::error('PayPal capture exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to capture PayPal order'], 500);
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
