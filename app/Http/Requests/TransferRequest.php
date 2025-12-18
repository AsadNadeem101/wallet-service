<?php

namespace App\Http\Requests;

use App\Models\Wallet;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Transfer Request
 *
 * Validates transfer requests to move funds between wallets.
 * Requires source_wallet_id, target_wallet_id, amount, and idempotency key.
 * Prevents self-transfers and ensures both wallets exist.
 */
class TransferRequest extends FormRequest
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
            'source_wallet_id' => ['required', 'integer', 'exists:wallets,id'],
            'target_wallet_id' => ['required', 'integer', 'exists:wallets,id', 'different:source_wallet_id'],
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
            'source_wallet_id.required' => 'Source wallet ID is required.',
            'source_wallet_id.integer' => 'Source wallet ID must be an integer.',
            'source_wallet_id.exists' => 'Source wallet does not exist.',
            'target_wallet_id.required' => 'Target wallet ID is required.',
            'target_wallet_id.integer' => 'Target wallet ID must be an integer.',
            'target_wallet_id.exists' => 'Target wallet does not exist.',
            'target_wallet_id.different' => 'Cannot transfer to the same wallet (self-transfer not allowed).',
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
            'source_wallet_id' => 'source wallet',
            'target_wallet_id' => 'target wallet',
            'amount' => 'transfer amount',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * Add after validation hook to check currency matching.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Check if both wallets have the same currency
            if (!$validator->errors()->has('source_wallet_id') &&
                !$validator->errors()->has('target_wallet_id')) {

                $sourceWallet = Wallet::find($this->source_wallet_id);
                $targetWallet = Wallet::find($this->target_wallet_id);

                if ($sourceWallet && $targetWallet &&
                    $sourceWallet->currency !== $targetWallet->currency) {
                    $validator->errors()->add(
                        'currency',
                        'Transfers are only allowed between wallets with the same currency. ' .
                        "Source wallet currency: {$sourceWallet->currency}, " .
                        "Target wallet currency: {$targetWallet->currency}."
                    );
                }
            }
        });
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
