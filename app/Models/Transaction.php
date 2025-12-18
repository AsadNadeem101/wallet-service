<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Transaction Model
 *
 * Represents a financial transaction in the wallet system.
 * Implements double-entry accounting where transfers create two records:
 * - One transfer_debit record (reduces source wallet)
 * - One transfer_credit record (increases target wallet)
 *
 * Features:
 * - Idempotency support via idempotency_key
 * - Balance snapshots for audit trail
 * - Support for deposits, withdrawals, and transfers
 * - Flexible metadata storage
 *
 * @property int $id Transaction's unique identifier
 * @property int $wallet_id ID of the wallet this transaction belongs to
 * @property TransactionType $type Transaction type enum
 * @property int $amount Transaction amount in minor units (always positive)
 * @property int $balance_after Wallet balance after this transaction in minor units
 * @property int|null $related_wallet_id For transfers: the other wallet involved
 * @property string|null $idempotency_key Unique key to prevent duplicate operations
 * @property array|null $metadata Additional transaction data as JSON
 * @property Carbon $created_at Creation timestamp
 * @property Carbon $updated_at Last update timestamp
 * @property-read Wallet $wallet The wallet this transaction belongs to
 * @property-read Wallet|null $relatedWallet The other wallet in a transfer
 */
class Transaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'balance_after',
        'related_wallet_id',
        'idempotency_key',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => TransactionType::class,
        'wallet_id' => 'integer',
        'amount' => 'integer',
        'balance_after' => 'integer',
        'related_wallet_id' => 'integer',
        'metadata' => 'array',
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
     * Get the wallet that this transaction belongs to.
     *
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the related wallet for transfer transactions.
     *
     * For transfer_debit: this is the target wallet (where money went)
     * For transfer_credit: this is the source wallet (where money came from)
     *
     * @return BelongsTo<Wallet, $this>
     */
    public function relatedWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }

    /**
     * Check if this transaction is a deposit.
     *
     * @return bool
     */
    public function isDeposit(): bool
    {
        return $this->type->isDeposit();
    }

    /**
     * Check if this transaction is a withdrawal.
     *
     * @return bool
     */
    public function isWithdrawal(): bool
    {
        return $this->type->isWithdrawal();
    }

    /**
     * Check if this transaction is a transfer (debit or credit).
     *
     * @return bool
     */
    public function isTransfer(): bool
    {
        return $this->type->isTransfer();
    }

    /**
     * Check if this transaction is a transfer debit (outgoing).
     *
     * @return bool
     */
    public function isTransferDebit(): bool
    {
        return $this->type === TransactionType::TRANSFER_DEBIT;
    }

    /**
     * Check if this transaction is a transfer credit (incoming).
     *
     * @return bool
     */
    public function isTransferCredit(): bool
    {
        return $this->type === TransactionType::TRANSFER_CREDIT;
    }

    /**
     * Get the amount in major units (e.g., dollars instead of cents).
     *
     * @return float
     */
    public function getAmountInMajorUnits(): float
    {
        return $this->amount / 100;
    }

    /**
     * Get the balance_after in major units.
     *
     * @return float
     */
    public function getBalanceAfterInMajorUnits(): float
    {
        return $this->balance_after / 100;
    }

    /**
     * Scope a query to filter by transaction type.
     *
     * @param Builder $query
     * @param TransactionType|string $type Transaction type (enum or string)
     * @return Builder
     */
    public function scopeOfType($query, TransactionType|string $type)
    {
        $value = $type instanceof TransactionType ? $type->value : $type;
        return $query->where('type', $value);
    }

    /**
     * Scope a query to filter by wallet.
     *
     * @param Builder $query
     * @param int $walletId Wallet ID
     * @return Builder
     */
    public function scopeForWallet($query, int $walletId)
    {
        return $query->where('wallet_id', $walletId);
    }

    /**
     * Scope a query to filter by idempotency key.
     *
     * @param Builder $query
     * @param string $key Idempotency key
     * @return Builder
     */
    public function scopeByIdempotencyKey($query, string $key)
    {
        return $query->where('idempotency_key', $key);
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param Builder $query
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @return Builder
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to get only deposits.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', TransactionType::DEPOSIT);
    }

    /**
     * Scope a query to get only withdrawals.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('type', TransactionType::WITHDRAWAL);
    }

    /**
     * Scope a query to get only transfers.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTransfers($query)
    {
        return $query->whereIn('type', [
            TransactionType::TRANSFER_DEBIT,
            TransactionType::TRANSFER_CREDIT,
        ]);
    }
}
