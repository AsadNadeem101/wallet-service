<?php

namespace App\Contracts;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;

/**
 * Wallet Repository Interface
 *
 * Defines the contract for wallet data access operations.
 * Implementations handle all database interactions for wallets.
 */
interface WalletRepositoryInterface
{
    /**
     * Find a wallet by ID.
     *
     * @param int $id
     * @return Wallet|null
     */
    public function findById(int $id): ?Wallet;

    /**
     * Find a wallet by ID or fail.
     *
     * @param int $id
     * @return Wallet
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByIdOrFail(int $id): Wallet;

    /**
     * Create a new wallet.
     *
     * @param array $data
     * @return Wallet
     */
    public function create(array $data): Wallet;

    /**
     * Update a wallet.
     *
     * @param Wallet $wallet
     * @param array $data
     * @return bool
     */
    public function update(Wallet $wallet, array $data): bool;

    /**
     * Get all wallets with optional filters.
     *
     * @param array $filters
     * @return Collection<int, Wallet>
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Lock a wallet for update and retrieve it.
     *
     * @param int $id
     * @return Wallet
     */
    public function lockForUpdate(int $id): Wallet;

    /**
     * Lock multiple wallets for update (ordered by ID to prevent deadlocks).
     *
     * @param array $ids
     * @return Collection<int, Wallet>
     */
    public function lockMultipleForUpdate(array $ids): Collection;

    /**
     * Increment wallet balance.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return void
     */
    public function incrementBalance(Wallet $wallet, int $amount): void;

    /**
     * Decrement wallet balance.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return void
     */
    public function decrementBalance(Wallet $wallet, int $amount): void;
}
