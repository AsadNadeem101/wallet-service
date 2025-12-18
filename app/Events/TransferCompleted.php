<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Transfer Completed Event
 *
 * Dispatched when a transfer between wallets is successfully completed.
 */
class TransferCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * The source wallet (funds debited from).
     *
     * @var Wallet
     */
    public Wallet $sourceWallet;

    /**
     * The target wallet (funds credited to).
     *
     * @var Wallet
     */
    public Wallet $targetWallet;

    /**
     * The debit transaction.
     *
     * @var Transaction
     */
    public Transaction $debitTransaction;

    /**
     * The credit transaction.
     *
     * @var Transaction
     */
    public Transaction $creditTransaction;

    /**
     * Whether this was a duplicate request (idempotency).
     *
     * @var bool
     */
    public bool $isDuplicate;

    /**
     * Create a new event instance.
     *
     * @param Wallet $sourceWallet
     * @param Wallet $targetWallet
     * @param Transaction $debitTransaction
     * @param Transaction $creditTransaction
     * @param bool $isDuplicate
     */
    public function __construct(
        Wallet $sourceWallet,
        Wallet $targetWallet,
        Transaction $debitTransaction,
        Transaction $creditTransaction,
        bool $isDuplicate = false
    ) {
        $this->sourceWallet = $sourceWallet;
        $this->targetWallet = $targetWallet;
        $this->debitTransaction = $debitTransaction;
        $this->creditTransaction = $creditTransaction;
        $this->isDuplicate = $isDuplicate;
    }
}
