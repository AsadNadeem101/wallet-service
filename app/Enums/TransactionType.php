<?php

namespace App\Enums;

/**
 * Transaction Type Enum
 *
 * Defines the possible types of wallet transactions.
 */
enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case TRANSFER_DEBIT = 'transfer_debit';
    case TRANSFER_CREDIT = 'transfer_credit';

    /**
     * Get all transaction type values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Deposit',
            self::WITHDRAWAL => 'Withdrawal',
            self::TRANSFER_DEBIT => 'Transfer Out',
            self::TRANSFER_CREDIT => 'Transfer In',
        };
    }

    /**
     * Check if transaction is a deposit.
     *
     * @return bool
     */
    public function isDeposit(): bool
    {
        return $this === self::DEPOSIT;
    }

    /**
     * Check if transaction is a withdrawal.
     *
     * @return bool
     */
    public function isWithdrawal(): bool
    {
        return $this === self::WITHDRAWAL;
    }

    /**
     * Check if transaction is a transfer.
     *
     * @return bool
     */
    public function isTransfer(): bool
    {
        return $this === self::TRANSFER_DEBIT || $this === self::TRANSFER_CREDIT;
    }

    /**
     * Check if transaction adds funds to wallet.
     *
     * @return bool
     */
    public function isCredit(): bool
    {
        return $this === self::DEPOSIT || $this === self::TRANSFER_CREDIT;
    }

    /**
     * Check if transaction removes funds from wallet.
     *
     * @return bool
     */
    public function isDebit(): bool
    {
        return $this === self::WITHDRAWAL || $this === self::TRANSFER_DEBIT;
    }
}
