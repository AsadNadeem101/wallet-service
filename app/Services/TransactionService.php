<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Transaction Service
 *
 * Handles business logic for transaction operations including:
 * - Retrieving transaction history
 * - Filtering transactions by type, date range
 * - Pagination support
 */
class TransactionService
{
    /**
     * Transaction repository instance.
     *
     * @var TransactionRepositoryInterface
     */
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * Constructor - Dependency Injection.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Get transaction history for a wallet.
     *
     * Supports filtering by:
     * - type: deposit, withdrawal, transfer_debit, transfer_credit
     * - start_date: Filter transactions from this date (Y-m-d format)
     * - end_date: Filter transactions until this date (Y-m-d format)
     * - per_page: Number of results per page (default: 15)
     *
     * @param Wallet $wallet The wallet to get transactions for
     * @param array $filters Associative array of filters
     * @return LengthAwarePaginator
     */
    public function getTransactionHistory(Wallet $wallet, array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;

        return $this->transactionRepository->getPaginatedByWallet($wallet->id, $filters, $perPage);
    }

    /**
     * Get a single transaction by ID.
     *
     * @param int $transactionId
     * @return Transaction|null
     */
    public function getTransaction(int $transactionId): ?Transaction
    {
        return $this->transactionRepository->findById($transactionId);
    }

    /**
     * Get transaction statistics for a wallet.
     *
     * Returns total deposits, withdrawals, transfers, etc.
     *
     * @param Wallet $wallet
     * @return array
     */
    public function getStatistics(Wallet $wallet): array
    {
        $transactions = $this->transactionRepository->getByWallet($wallet->id);

        return [
            'total_transactions' => $transactions->count(),
            'total_deposits' => $transactions->where('type', TransactionType::DEPOSIT->value)->sum('amount'),
            'total_withdrawals' => $transactions->where('type', TransactionType::WITHDRAWAL->value)->sum('amount'),
            'total_transfers_in' => $transactions->where('type', TransactionType::TRANSFER_CREDIT->value)->sum('amount'),
            'total_transfers_out' => $transactions->where('type', TransactionType::TRANSFER_DEBIT->value)->sum('amount'),
        ];
    }
}
