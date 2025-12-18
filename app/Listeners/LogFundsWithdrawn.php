<?php

namespace App\Listeners;

use App\Events\FundsWithdrawn;
use Illuminate\Support\Facades\Log;

/**
 * Log Funds Withdrawn Listener
 *
 * Logs withdrawal events to the application log.
 */
class LogFundsWithdrawn
{
    /**
     * Handle the event.
     *
     * @param FundsWithdrawn $event
     * @return void
     */
    public function handle(FundsWithdrawn $event): void
    {
        if ($event->isDuplicate) {
            Log::info('Duplicate withdrawal request (idempotency)', [
                'wallet_id' => $event->wallet->id,
                'amount' => $event->transaction->amount,
                'idempotency_key' => $event->transaction->idempotency_key,
                'original_transaction_id' => $event->transaction->id,
            ]);
        } else {
            Log::info('Withdrawal successful', [
                'transaction_id' => $event->transaction->id,
                'wallet_id' => $event->wallet->id,
                'amount' => $event->transaction->amount,
                'balance_after' => $event->transaction->balance_after,
                'idempotency_key' => $event->transaction->idempotency_key,
            ]);
        }
    }
}
