<?php

namespace App\Exceptions\Wallet;

use Exception;

/**
 * Insufficient Balance Exception
 *
 * Thrown when a withdrawal or transfer cannot be completed
 * due to insufficient funds in the wallet.
 */
class InsufficientBalanceException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param int $currentBalance Current balance in minor units
     * @param int $requestedAmount Requested amount in minor units
     * @param int $walletId Wallet ID
     */
    public function __construct(int $currentBalance, int $requestedAmount, int $walletId)
    {
        $message = sprintf(
            'Insufficient balance in wallet #%d. Current balance: %d, Requested: %d',
            $walletId,
            $currentBalance,
            $requestedAmount
        );

        parent::__construct($message, 422);
    }
}
