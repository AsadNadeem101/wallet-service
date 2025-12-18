<?php

namespace App\Providers;

use App\Events\FundsDeposited;
use App\Events\FundsWithdrawn;
use App\Events\TransactionFailed;
use App\Events\TransferCompleted;
use App\Events\WalletCreated;
use App\Listeners\LogFundsDeposited;
use App\Listeners\LogFundsWithdrawn;
use App\Listeners\LogTransactionFailed;
use App\Listeners\LogTransferCompleted;
use App\Listeners\LogWalletCreated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event Service Provider
 *
 * Registers event listeners for the application.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        WalletCreated::class => [
            LogWalletCreated::class,
        ],
        FundsDeposited::class => [
            LogFundsDeposited::class,
        ],
        FundsWithdrawn::class => [
            LogFundsWithdrawn::class,
        ],
        TransferCompleted::class => [
            LogTransferCompleted::class,
        ],
        TransactionFailed::class => [
            LogTransactionFailed::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
