<?php

namespace App\Exceptions\Wallet;

use Exception;

/**
 * Currency Mismatch Exception
 *
 * Thrown when attempting to transfer between wallets with different currencies.
 */
class CurrencyMismatchException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $sourceCurrency Source wallet currency
     * @param string $targetCurrency Target wallet currency
     */
    public function __construct(string $sourceCurrency, string $targetCurrency)
    {
        $message = sprintf(
            'Currency mismatch. Source wallet: %s, Target wallet: %s. Transfers are only allowed between wallets with the same currency.',
            $sourceCurrency,
            $targetCurrency
        );

        parent::__construct($message, 422);
    }
}
