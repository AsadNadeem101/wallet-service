<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Wallet;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Transaction Controller
 *
 * Handles HTTP requests for transaction history and information.
 * Supports filtering by type, date range, and pagination.
 */
class TransactionController extends Controller
{
    /**
     * Transaction service instance.
     *
     * @var TransactionService
     */
    protected TransactionService $transactionService;

    /**
     * Constructor - Dependency Injection.
     *
     * @param TransactionService $transactionService
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get transaction history for a wallet.
     *
     * GET /api/wallets/{wallet}/transactions
     *
     * Supports query parameters:
     * - type: deposit, withdrawal, transfer_debit, transfer_credit
     * - start_date: Y-m-d format
     * - end_date: Y-m-d format
     * - per_page: Number of results per page (default: 15)
     *
     * @param Request $request
     * @param Wallet $wallet
     * @return JsonResponse
     */
    public function index(Request $request, Wallet $wallet): JsonResponse
    {
        $filters = $request->only(['type', 'start_date', 'end_date', 'per_page']);

        $transactions = $this->transactionService->getTransactionHistory($wallet, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully.',
            'data' => TransactionResource::collection($transactions->items()),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
            ],
        ], 200);
    }
}
