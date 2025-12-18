<?php

namespace App\Contracts;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Transaction Repository Interface
 *
 * Defines the contract for transaction data access operations.
 * Implementations handle all database interactions for transactions.
 */
interface TransactionRepositoryInterface
{
    /**
     * Find a transaction by ID.
     *
     * @param int $id
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction;

    /**
     * Create a new transaction.
     *
     * @param array $data
     * @return Transaction
     */
    public function create(array $data): Transaction;

    /**
     * Find transaction by idempotency key, wallet ID, and type.
     *
     * @param string $idempotencyKey
     * @param int $walletId
     * @param string $type
     * @return Transaction|null
     */
    public function findByIdempotencyKey(string $idempotencyKey, int $walletId, string $type): ?Transaction;

    /**
     * Get paginated transactions for a wallet with optional filters.
     *
     * @param int $walletId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedByWallet(int $walletId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all transactions for a wallet.
     *
     * @param int $walletId
     * @return Collection
     */
    public function getByWallet(int $walletId): Collection;

    /**
     * Get transactions by type.
     *
     * @param string $type
     * @return Collection
     */
    public function getByType(string $type): Collection;
}
