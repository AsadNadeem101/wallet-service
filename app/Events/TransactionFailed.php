<?php

namespace App\Events;

use App\Models\Wallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Transaction Failed Event
 *
 * Dispatched when a transaction fails (insufficient balance, validation errors, etc.).
 */
class TransactionFailed
{
    use Dispatchable, SerializesModels;

    /**
     * The wallet involved in the failed transaction.
     *
     * @var Wallet
     */
    public Wallet $wallet;

    /**
     * The type of transaction that failed.
     *
     * @var string
     */
    public string $transactionType;

    /**
     * The exception that caused the failure.
     *
     * @var Throwable
     */
    public Throwable $exception;

    /**
     * Additional context about the failure.
     *
     * @var array
     */
    public array $context;

    /**
     * Create a new event instance.
     *
     * @param Wallet $wallet
     * @param string $transactionType
     * @param Throwable $exception
     * @param array $context
     */
    public function __construct(Wallet $wallet, string $transactionType, Throwable $exception, array $context = [])
    {
        $this->wallet = $wallet;
        $this->transactionType = $transactionType;
        $this->exception = $exception;
        $this->context = $context;
    }
}
