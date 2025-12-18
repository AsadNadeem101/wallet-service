<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Funds Withdrawn Event
 *
 * Dispatched when funds are successfully withdrawn from a wallet.
 */
class FundsWithdrawn
{
    use Dispatchable, SerializesModels;

    /**
     * The wallet that funds were withdrawn from.
     *
     * @var Wallet
     */
    public Wallet $wallet;

    /**
     * The withdrawal transaction.
     *
     * @var Transaction
     */
    public Transaction $transaction;

    /**
     * Whether this was a duplicate request (idempotency).
     *
     * @var bool
     */
    public bool $isDuplicate;

    /**
     * Create a new event instance.
     *
     * @param Wallet $wallet
     * @param Transaction $transaction
     * @param bool $isDuplicate
     */
    public function __construct(Wallet $wallet, Transaction $transaction, bool $isDuplicate = false)
    {
        $this->wallet = $wallet;
        $this->transaction = $transaction;
        $this->isDuplicate = $isDuplicate;
    }
}
