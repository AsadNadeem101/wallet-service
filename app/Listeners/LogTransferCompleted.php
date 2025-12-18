<?php

namespace App\Listeners;

use App\Events\TransferCompleted;
use Illuminate\Support\Facades\Log;

/**
 * Log Transfer Completed Listener
 *
 * Logs transfer events to the application log.
 */
class LogTransferCompleted
{
    /**
     * Handle the event.
     *
     * @param TransferCompleted $event
     * @return void
     */
    public function handle(TransferCompleted $event): void
    {
        if ($event->isDuplicate) {
            Log::info('Duplicate transfer request (idempotency)', [
                'source_wallet_id' => $event->sourceWallet->id,
                'target_wallet_id' => $event->targetWallet->id,
                'amount' => $event->debitTransaction->amount,
                'idempotency_key' => $event->debitTransaction->idempotency_key,
                'original_debit_transaction_id' => $event->debitTransaction->id,
                'original_credit_transaction_id' => $event->creditTransaction->id,
            ]);
        } else {
            Log::info('Transfer successful', [
                'debit_transaction_id' => $event->debitTransaction->id,
                'credit_transaction_id' => $event->creditTransaction->id,
                'source_wallet_id' => $event->sourceWallet->id,
                'target_wallet_id' => $event->targetWallet->id,
                'amount' => $event->debitTransaction->amount,
                'source_balance_after' => $event->debitTransaction->balance_after,
                'target_balance_after' => $event->creditTransaction->balance_after,
                'idempotency_key' => $event->debitTransaction->idempotency_key,
            ]);
        }
    }
}
