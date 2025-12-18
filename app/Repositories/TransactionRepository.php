<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Transaction Repository
 *
 * Concrete implementation of TransactionRepositoryInterface.
 * Handles all database operations for transactions.
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Transaction model instance.
     *
     * @var Transaction
     */
    protected Transaction $model;

    /**
     * Constructor.
     *
     * @param Transaction $model
     */
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    /**
     * Find a transaction by ID.
     *
     * @param int $id
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction
    {
        return $this->model->find($id);
    }

    /**
     * Create a new transaction.
     *
     * @param array $data
     * @return Transaction
     */
    public function create(array $data): Transaction
    {
        return $this->model->create($data);
    }

    /**
     * Find transaction by idempotency key, wallet ID, and type.
     *
     * @param string $idempotencyKey
     * @param int $walletId
     * @param string $type
     * @return Transaction|null
     */
    public function findByIdempotencyKey(string $idempotencyKey, int $walletId, string $type): ?Transaction
    {
        return $this->model
            ->where('idempotency_key', $idempotencyKey)
            ->where('wallet_id', $walletId)
            ->where('type', $type)
            ->first();
    }

    /**
     * Get paginated transactions for a wallet with optional filters.
     *
     * @param int $walletId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedByWallet(int $walletId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('wallet_id', $walletId);

        // Apply type filter
        if (isset($filters['type'])) {
            $query->ofType($filters['type']);
        }

        // Apply date range filter
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get all transactions for a wallet.
     *
     * @param int $walletId
     * @return Collection
     */
    public function getByWallet(int $walletId): Collection
    {
        return $this->model
            ->where('wallet_id', $walletId)
            ->latest()
            ->get();
    }

    /**
     * Get transactions by type.
     *
     * @param string $type
     * @return Collection
     */
    public function getByType(string $type): Collection
    {
        return $this->model
            ->ofType($type)
            ->latest()
            ->get();
    }
}
