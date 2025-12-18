<?php

namespace App\Listeners;

use App\Events\TransactionFailed;
use Illuminate\Support\Facades\Log;

/**
 * Log Transaction Failed Listener
 *
 * Logs failed transaction events to the application log.
 */
class LogTransactionFailed
{
    /**
     * Handle the event.
     *
     * @param TransactionFailed $event
     * @return void
     */
    public function handle(TransactionFailed $event): void
    {
        Log::warning($event->transactionType . ' failed - ' . $event->exception->getMessage(), array_merge([
            'wallet_id' => $event->wallet->id,
            'exception' => get_class($event->exception),
            'message' => $event->exception->getMessage(),
        ], $event->context));
    }
}
