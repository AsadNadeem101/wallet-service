<?php

namespace App\Exceptions\Wallet;

use Exception;

/**
 * Invalid Amount Exception
 *
 * Thrown when an amount is invalid (zero, negative, or missing).
 */
class InvalidAmountException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param int $amount The invalid amount
     * @param string $operation The operation being attempted
     */
    public function __construct(int $amount, string $operation = 'transaction')
    {
        $message = sprintf(
            'Invalid amount for %s: %d. Amount must be greater than zero.',
            $operation,
            $amount
        );

        parent::__construct($message, 422);
    }
}
