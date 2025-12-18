<?php

namespace App\Events;

use App\Models\Wallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Wallet Created Event
 *
 * Dispatched when a new wallet is created.
 */
class WalletCreated
{
    use Dispatchable, SerializesModels;

    /**
     * The created wallet.
     *
     * @var Wallet
     */
    public Wallet $wallet;

    /**
     * Create a new event instance.
     *
     * @param Wallet $wallet
     */
    public function __construct(Wallet $wallet)
    {
        $this->wallet = $wallet;
    }
}
