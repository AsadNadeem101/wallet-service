<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\WalletRepositoryInterface;
use App\Enums\TransactionType;
use App\Events\FundsDeposited;
use App\Events\FundsWithdrawn;
use App\Events\TransactionFailed;
use App\Events\WalletCreated;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Exceptions\Wallet\InvalidAmountException;
use App\Models\Transaction;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * Wallet Service
 *
 * Handles business logic for wallet operations including:
 * - Creating wallets
 * - Depositing funds
 * - Withdrawing funds
 * - Retrieving wallet information and balances
 *
 * Implements idempotency for deposits and withdrawals.
 */
class WalletService
{
    /**
     * Wallet repository instance.
     *
     * @var WalletRepositoryInterface
     */
    protected WalletRepositoryInterface $walletRepository;

    /**
     * Transaction repository instance.
     *
     * @var TransactionRepositoryInterface
     */
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * Constructor - Dependency Injection.
     *
     * @param WalletRepositoryInterface $walletRepository
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        WalletRepositoryInterface $walletRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
    }
    /**
     * Create a new wallet.
     *
     * @param string $ownerName Owner's name
     * @param string $currency ISO 4217 currency code (e.g., USD, EUR)
     * @return Wallet
     */
    public function createWallet(string $ownerName, string $currency): Wallet
    {
        $wallet = $this->walletRepository->create([
            'owner_name' => $ownerName,
            'currency' => strtoupper($currency),
            'balance' => 0,
        ]);

        WalletCreated::dispatch($wallet);

        return $wallet;
    }

    /**
     * Get a wallet by ID.
     *
     * @param int $walletId
     * @return Wallet|null
     */
    public function getWallet(int $walletId): ?Wallet
    {
        return $this->walletRepository->findById($walletId);
    }

    /**
     * List all wallets with optional filters.
     *
     * @param array $filters Associative array with 'owner' and/or 'currency' keys
     * @return Collection<int, Wallet>
     */
    public function listWallets(array $filters = []): Collection
    {
        return $this->walletRepository->getAll($filters);
    }

    /**
     * Deposit funds into a wallet.
     *
     * Implements idempotency: if the same idempotency key is used,
     * returns the original transaction instead of creating a duplicate.
     *
     * @param Wallet $wallet The wallet to deposit into
     * @param int $amount Amount in minor units (e.g., cents)
     * @param string|null $idempotencyKey Unique key to prevent duplicates
     * @return Transaction
     * @throws Exception|Throwable If amount is invalid
     */
    public function deposit(Wallet $wallet, int $amount, ?string $idempotencyKey = null): Transaction
    {
        if ($amount <= 0) {
            throw new InvalidAmountException($amount, 'deposit');
        }

        // Check for idempotency
        if ($idempotencyKey) {
            $existingTransaction = $this->transactionRepository->findByIdempotencyKey(
                $idempotencyKey,
                $wallet->id,
                TransactionType::DEPOSIT->value
            );

            if ($existingTransaction) {
                FundsDeposited::dispatch($wallet, $existingTransaction, true);

                return $existingTransaction;
            }
        }

        $transaction = DB::transaction(function () use ($wallet, $amount, $idempotencyKey) {
            // Lock the wallet row for update
            $wallet = $this->walletRepository->lockForUpdate($wallet->id);

            // Update balance
            $this->walletRepository->incrementBalance($wallet, $amount);

            // Create transaction record
            return $this->transactionRepository->create([
                'wallet_id' => $wallet->id,
                'type' => TransactionType::DEPOSIT,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'related_wallet_id' => null,
                'idempotency_key' => $idempotencyKey,
                'metadata' => ['description' => 'Deposit'],
            ]);
        });

        FundsDeposited::dispatch($wallet, $transaction, false);

        return $transaction;
    }

    /**
     * Withdraw funds from a wallet.
     *
     * Implements idempotency and validates sufficient balance.
     *
     * @param Wallet $wallet The wallet to withdraw from
     * @param int $amount Amount in minor units (e.g., cents)
     * @param string|null $idempotencyKey Unique key to prevent duplicates
     * @return Transaction
     * @throws \Exception If insufficient balance or invalid amount
     */
    public function withdraw(Wallet $wallet, int $amount, ?string $idempotencyKey = null): Transaction
    {
        if ($amount <= 0) {
            throw new InvalidAmountException($amount, 'withdrawal');
        }

        // Check for idempotency
        if ($idempotencyKey) {
            $existingTransaction = $this->transactionRepository->findByIdempotencyKey(
                $idempotencyKey,
                $wallet->id,
                TransactionType::WITHDRAWAL->value
            );

            if ($existingTransaction) {
                FundsWithdrawn::dispatch($wallet, $existingTransaction, true);

                return $existingTransaction;
            }
        }

        $transaction = DB::transaction(function () use ($wallet, $amount, $idempotencyKey) {
            // Lock the wallet row for update
            $wallet = $this->walletRepository->lockForUpdate($wallet->id);

            // Check sufficient balance
            if ($wallet->balance < $amount) {
                $exception = new InsufficientBalanceException($wallet->balance, $amount, $wallet->id);

                TransactionFailed::dispatch($wallet, 'withdrawal', $exception, [
                    'current_balance' => $wallet->balance,
                    'requested_amount' => $amount,
                ]);

                throw $exception;
            }

            // Update balance
            $this->walletRepository->decrementBalance($wallet, $amount);

            // Create transaction record
            return $this->transactionRepository->create([
                'wallet_id' => $wallet->id,
                'type' => TransactionType::WITHDRAWAL,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'related_wallet_id' => null,
                'idempotency_key' => $idempotencyKey,
                'metadata' => ['description' => 'Withdrawal'],
            ]);
        });

        FundsWithdrawn::dispatch($wallet, $transaction, false);

        return $transaction;
    }

    /**
     * Get wallet balance.
     *
     * @param Wallet $wallet
     * @return int Balance in minor units
     */
    public function getBalance(Wallet $wallet): int
    {
        return $wallet->balance;
    }
}
