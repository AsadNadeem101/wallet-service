<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Wallet Model
 *
 * Represents a digital wallet that holds a balance in a specific currency.
 * Each wallet has an owner name and can have multiple transactions.
 * Balances are stored in minor units (cents) to avoid floating-point issues.
 *
 * @property int $id Wallet's unique identifier
 * @property string $owner_name Name of the wallet owner
 * @property string $currency ISO 4217 currency code (USD, EUR, etc.)
 * @property int $balance Current balance in minor units (e.g., cents)
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Last update timestamp
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions Collection of wallet transactions
 * @property-read int|null $transactions_count Number of transactions
 */
class Wallet extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_name',
        'currency',
        'balance',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'balance' => 0,
    ];

    /**
     * Get all transactions for this wallet.
     *
     * Returns transactions in descending chronological order (newest first).
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    /**
     * Get the balance in major units (e.g., dollars instead of cents).
     *
     * Converts the stored integer balance to a decimal representation.
     * Example: 1050 cents -> 10.50 dollars
     *
     * @return float
     */
    public function getBalanceInMajorUnits(): float
    {
        return $this->balance / 100;
    }

    /**
     * Convert an amount from major units to minor units.
     *
     * Static helper method to convert decimal amounts to integers.
     * Example: 10.50 dollars -> 1050 cents
     *
     * @param float $amount Amount in major units
     * @return int Amount in minor units
     */
    public static function toMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Check if the wallet has sufficient balance for a transaction.
     *
     * @param int $amount Amount to check in minor units
     * @return bool True if sufficient balance exists
     */
    public function hasSufficientBalance(int $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Scope a query to filter wallets by currency.
     *
     * @param Builder $query
     * @param string $currency Currency code (USD, EUR, etc.)
     * @return Builder
     */
    public function scopeCurrency($query, string $currency)
    {
        return $query->where('currency', strtoupper($currency));
    }

    /**
     * Scope a query to filter wallets by owner name.
     *
     * @param Builder $query
     * @param string $ownerName Owner name
     * @return Builder
     */
    public function scopeForOwner($query, string $ownerName)
    {
        return $query->where('owner_name', $ownerName);
    }
}
