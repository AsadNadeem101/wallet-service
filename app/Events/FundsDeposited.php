<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Funds Deposited Event
 *
 * Dispatched when funds are successfully deposited into a wallet.
 */
class FundsDeposited
{
    use Dispatchable, SerializesModels;

    /**
     * The wallet that received the deposit.
     *
     * @var Wallet
     */
    public Wallet $wallet;

    /**
     * The deposit transaction.
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
