<?php

namespace App\Repositories;

use App\Contracts\WalletRepositoryInterface;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Wallet Repository
 *
 * Concrete implementation of WalletRepositoryInterface.
 * Handles all database operations for wallets.
 */
class WalletRepository implements WalletRepositoryInterface
{
    /**
     * Wallet model instance.
     *
     * @var Wallet
     */
    protected Wallet $model;

    /**
     * Constructor.
     *
     * @param Wallet $model
     */
    public function __construct(Wallet $model)
    {
        $this->model = $model;
    }

    /**
     * Find a wallet by ID.
     *
     * @param int $id
     * @return Wallet|null
     */
    public function findById(int $id): ?Wallet
    {
        return $this->model->find($id);
    }

    /**
     * Find a wallet by ID or fail.
     *
     * @param int $id
     * @return Wallet
     * @throws ModelNotFoundException
     */
    public function findByIdOrFail(int $id): Wallet
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new wallet.
     *
     * @param array $data
     * @return Wallet
     */
    public function create(array $data): Wallet
    {
        return $this->model->create($data);
    }

    /**
     * Update a wallet.
     *
     * @param Wallet $wallet
     * @param array $data
     * @return bool
     */
    public function update(Wallet $wallet, array $data): bool
    {
        return $wallet->update($data);
    }

    /**
     * Get all wallets with optional filters.
     *
     * @param array $filters
     * @return Collection<int, Wallet>
     */
    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->query();

        if (isset($filters['owner'])) {
            $query->where('owner_name', 'like', '%' . $filters['owner'] . '%');
        }

        if (isset($filters['currency'])) {
            $query->currency($filters['currency']);
        }

        return $query->latest()->get();
    }

    /**
     * Lock a wallet for update and retrieve it.
     *
     * @param int $id
     * @return Wallet
     */
    public function lockForUpdate(int $id): Wallet
    {
        return $this->model->lockForUpdate()->findOrFail($id);
    }

    /**
     * Lock multiple wallets for update (ordered by ID to prevent deadlocks).
     *
     * @param array $ids
     * @return Collection<int, Wallet>
     */
    public function lockMultipleForUpdate(array $ids): Collection
    {
        return $this->model
            ->lockForUpdate()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get()
            ->keyBy('id');
    }

    /**
     * Increment wallet balance.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return void
     */
    public function incrementBalance(Wallet $wallet, int $amount): void
    {
        $wallet->balance += $amount;
        $wallet->save();
    }

    /**
     * Decrement wallet balance.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return void
     */
    public function decrementBalance(Wallet $wallet, int $amount): void
    {
        $wallet->balance -= $amount;
        $wallet->save();
    }
}
