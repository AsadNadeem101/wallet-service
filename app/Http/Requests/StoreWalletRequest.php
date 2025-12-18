<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Wallet Request
 *
 * Validates the request to create a new wallet.
 * Requires owner_name and currency (ISO 4217 code).
 * Wallet starts with zero balance.
 */
class StoreWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * No authentication required, always returns true.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'owner_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'owner_name.required' => 'Owner name is required.',
            'owner_name.string' => 'Owner name must be a valid string.',
            'owner_name.max' => 'Owner name cannot exceed 255 characters.',
            'currency.required' => 'Currency code is required.',
            'currency.size' => 'Currency must be a 3-letter ISO 4217 code (e.g., USD, EUR).',
            'currency.regex' => 'Currency must be uppercase letters only (e.g., USD, EUR, GBP).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'owner_name' => 'owner name',
            'currency' => 'currency code',
        ];
    }
}
