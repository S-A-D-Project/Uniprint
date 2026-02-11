<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutFromServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
        ];
    }
}
