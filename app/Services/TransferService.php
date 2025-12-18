<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\WalletRepositoryInterface;
use App\Enums\TransactionType;
use App\Events\TransactionFailed;
use App\Events\TransferCompleted;
use App\Exceptions\Wallet\CurrencyMismatchException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Exceptions\Wallet\InvalidAmountException;
use App\Exceptions\Wallet\SelfTransferException;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

/**
 * Transfer Service
 *
 * Handles business logic for transferring funds between wallets.
 * Implements double-entry accounting where each transfer creates
 * two transaction records (debit and credit) atomically.
 *
 * Features:
 * - Atomic operations (all-or-nothing)
 * - Idempotency support
 * - Currency validation
 * - Balance verification
 */
class TransferService
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
     * Transfer funds between two wallets.
     *
     * Creates two transaction records:
     * - transfer_debit: Deducts from source wallet
     * - transfer_credit: Adds to target wallet
     *
     * Both transactions share the same idempotency key.
     *
     * @param Wallet $sourceWallet Source wallet (money goes out)
     * @param Wallet $targetWallet Target wallet (money comes in)
     * @param int $amount Amount in minor units (e.g., cents)
     * @param string|null $idempotencyKey Unique key to prevent duplicates
     * @return array Array containing both transactions ['debit' => Transaction, 'credit' => Transaction]
     * @throws \Exception If validation fails or insufficient balance
     */
    public function transfer(
        Wallet $sourceWallet,
        Wallet $targetWallet,
        int $amount,
        ?string $idempotencyKey = null
    ): array {
        // Validate amount
        if ($amount <= 0) {
            throw new InvalidAmountException($amount, 'transfer');
        }

        // Prevent self-transfer
        if ($sourceWallet->id === $targetWallet->id) {
            throw new SelfTransferException($sourceWallet->id);
        }

        // Validate currency match
        if ($sourceWallet->currency !== $targetWallet->currency) {
            throw new CurrencyMismatchException($sourceWallet->currency, $targetWallet->currency);
        }

        // Check for idempotency
        if ($idempotencyKey) {
            $existingDebit = $this->transactionRepository->findByIdempotencyKey(
                $idempotencyKey,
                $sourceWallet->id,
                TransactionType::TRANSFER_DEBIT->value
            );

            if ($existingDebit) {
                // Find the corresponding credit transaction
                $existingCredit = $this->transactionRepository->findByIdempotencyKey(
                    $idempotencyKey,
                    $targetWallet->id,
                    TransactionType::TRANSFER_CREDIT->value
                );

                TransferCompleted::dispatch(
                    $sourceWallet,
                    $targetWallet,
                    $existingDebit,
                    $existingCredit,
                    true
                );

                return [
                    'debit' => $existingDebit,
                    'credit' => $existingCredit,
                ];
            }
        }

        // Perform atomic transfer
        return DB::transaction(function () use ($sourceWallet, $targetWallet, $amount, $idempotencyKey) {
            // Lock both wallets for update (ordered by ID to prevent deadlocks)
            $wallets = $this->walletRepository->lockMultipleForUpdate([
                $sourceWallet->id,
                $targetWallet->id
            ]);

            $source = $wallets[$sourceWallet->id];
            $target = $wallets[$targetWallet->id];

            // Check sufficient balance
            if ($source->balance < $amount) {
                $exception = new InsufficientBalanceException($source->balance, $amount, $source->id);

                TransactionFailed::dispatch($source, 'transfer', $exception, [
                    'target_wallet_id' => $target->id,
                    'current_balance' => $source->balance,
                    'requested_amount' => $amount,
                ]);

                throw $exception;
            }

            // Update balances
            $this->walletRepository->decrementBalance($source, $amount);
            $this->walletRepository->incrementBalance($target, $amount);

            // Create debit transaction (source wallet)
            $debitTransaction = $this->transactionRepository->create([
                'wallet_id' => $source->id,
                'type' => TransactionType::TRANSFER_DEBIT,
                'amount' => $amount,
                'balance_after' => $source->balance,
                'related_wallet_id' => $target->id,
                'idempotency_key' => $idempotencyKey,
                'metadata' => [
                    'description' => 'Transfer to wallet #' . $target->id,
                    'target_wallet_id' => $target->id,
                    'target_wallet_owner' => $target->owner_name,
                ],
            ]);

            // Create credit transaction (target wallet)
            $creditTransaction = $this->transactionRepository->create([
                'wallet_id' => $target->id,
                'type' => TransactionType::TRANSFER_CREDIT,
                'amount' => $amount,
                'balance_after' => $target->balance,
                'related_wallet_id' => $source->id,
                'idempotency_key' => $idempotencyKey,
                'metadata' => [
                    'description' => 'Transfer from wallet #' . $source->id,
                    'source_wallet_id' => $source->id,
                    'source_wallet_owner' => $source->owner_name,
                ],
            ]);

            TransferCompleted::dispatch(
                $source,
                $target,
                $debitTransaction,
                $creditTransaction,
                false
            );

            return [
                'debit' => $debitTransaction,
                'credit' => $creditTransaction,
            ];
        });
    }
}
