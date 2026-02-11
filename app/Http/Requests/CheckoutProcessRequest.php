<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:gcash,cash,paypal',
            'fulfillment_method' => 'required|in:pickup,delivery',
            'requested_fulfillment_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'contact_name' => 'required|string|max:255',
            'rush_option' => 'required|in:standard,express,rush,same_day',
            'rush_fee' => 'nullable|numeric|min:0',
        ];
    }
}
