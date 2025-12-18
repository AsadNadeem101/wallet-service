<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Withdraw Request
 *
 * Validates withdrawal requests to remove funds from a wallet.
 * Requires amount (in minor units) and idempotency key.
 * Amount must be positive integer (no zero, no negative).
 * Business logic will verify sufficient balance.
 */
class WithdrawRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
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
            'amount.required' => 'Amount is required.',
            'amount.integer' => 'Amount must be an integer (in minor units, e.g., cents).',
            'amount.min' => 'Amount must be greater than zero.',
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
            'amount' => 'withdrawal amount',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Extract idempotency key from header if not in body.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Get idempotency key from header if not in body
        if (!$this->has('idempotency_key') && $this->header('Idempotency-Key')) {
            $this->merge([
                'idempotency_key' => $this->header('Idempotency-Key'),
            ]);
        }
    }
}
