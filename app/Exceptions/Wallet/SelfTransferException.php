<?php

namespace App\Exceptions\Wallet;

use Exception;

/**
 * Self Transfer Exception
 *
 * Thrown when attempting to transfer funds to the same wallet.
 */
class SelfTransferException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param int $walletId The wallet ID
     */
    public function __construct(int $walletId)
    {
        $message = sprintf(
            'Self-transfer not allowed. Cannot transfer funds within the same wallet (ID: %d).',
            $walletId
        );

        parent::__construct($message, 422);
    }
}
