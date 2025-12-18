<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transaction Resource
 *
 * Transforms Transaction model data into a consistent JSON response format.
 * Includes both minor units (cents) and major units (dollars) for amounts.
 */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'amount_formatted' => $this->getAmountInMajorUnits(),
            'balance_after' => $this->balance_after,
            'balance_after_formatted' => $this->getBalanceAfterInMajorUnits(),
            'related_wallet_id' => $this->related_wallet_id,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

}
