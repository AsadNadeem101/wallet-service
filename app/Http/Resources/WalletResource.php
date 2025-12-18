<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wallet Resource
 *
 * Transforms Wallet model data into a consistent JSON response format.
 * Includes both minor units (cents) and major units (dollars) for balance.
 */
class WalletResource extends JsonResource
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
            'owner_name' => $this->owner_name,
            'currency' => $this->currency,
            'balance' => $this->balance,
            'balance_formatted' => $this->getBalanceInMajorUnits(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

}
