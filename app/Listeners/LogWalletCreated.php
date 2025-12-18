<?php

namespace App\Listeners;

use App\Events\WalletCreated;
use Illuminate\Support\Facades\Log;

/**
 * Log Wallet Created Listener
 *
 * Logs wallet creation events to the application log.
 */
class LogWalletCreated
{
    /**
     * Handle the event.
     *
     * @param WalletCreated $event
     * @return void
     */
    public function handle(WalletCreated $event): void
    {
        Log::info('Wallet created', [
            'wallet_id' => $event->wallet->id,
            'owner_name' => $event->wallet->owner_name,
            'currency' => $event->wallet->currency,
        ]);
    }
}
