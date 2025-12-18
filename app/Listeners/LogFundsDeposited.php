<?php

namespace App\Listeners;

use App\Events\FundsDeposited;
use Illuminate\Support\Facades\Log;

/**
 * Log Funds Deposited Listener
 *
 * Logs deposit events to the application log.
 */
class LogFundsDeposited
{
    /**
     * Handle the event.
     *
     * @param FundsDeposited $event
     * @return void
     */
    public function handle(FundsDeposited $event): void
    {
        if ($event->isDuplicate) {
            Log::info('Duplicate deposit request (idempotency)', [
                'wallet_id' => $event->wallet->id,
                'amount' => $event->transaction->amount,
                'idempotency_key' => $event->transaction->idempotency_key,
                'original_transaction_id' => $event->transaction->id,
            ]);
        } else {
            Log::info('Deposit successful', [
                'transaction_id' => $event->transaction->id,
                'wallet_id' => $event->wallet->id,
                'amount' => $event->transaction->amount,
                'balance_after' => $event->transaction->balance_after,
                'idempotency_key' => $event->transaction->idempotency_key,
            ]);
        }
    }
}
