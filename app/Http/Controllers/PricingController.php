<?php

namespace App\Http\Controllers;

use App\Services\PricingEngine;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    protected $pricingEngine;

    public function __construct(PricingEngine $pricingEngine)
    {
        $this->pricingEngine = $pricingEngine;
    }

    /**
     * Calculate price for a service with customizations (AJAX)
     */
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'service_id' => 'required|uuid',
            'customizations' => 'nullable|array',
            'customizations.*' => 'uuid',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        try {
            $result = $this->pricingEngine->calculatePrice(
                $request->service_id,
                $request->customizations ?? [],
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

}
