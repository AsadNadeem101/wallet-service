<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Wallet;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

/**
 * Transfer Controller
 *
 * Handles HTTP requests for transferring funds between wallets.
 * Implements atomic double-entry transfers with idempotency.
 */
class TransferController extends Controller
{
    /**
     * Transfer service instance.
     *
     * @var TransferService
     */
    protected TransferService $transferService;

    /**
     * Constructor - Dependency Injection.
     *
     * @param TransferService $transferService
     */
    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Transfer funds between wallets.
     *
     * POST /api/transfers
     *
     * Creates two transaction records (debit and credit) atomically.
     *
     * @param TransferRequest $request
     * @return JsonResponse
     */
    public function store(TransferRequest $request): JsonResponse
    {
        $sourceWallet = Wallet::findOrFail($request->input('source_wallet_id'));
        $targetWallet = Wallet::findOrFail($request->input('target_wallet_id'));

        $transactions = $this->transferService->transfer(
            $sourceWallet,
            $targetWallet,
            $request->input('amount'),
            $request->input('idempotency_key')
        );

        return response()->json([
            'success' => true,
            'message' => 'Transfer completed successfully.',
            'data' => [
                'debit_transaction' => new TransactionResource($transactions['debit']),
                'credit_transaction' => new TransactionResource($transactions['credit']),
                'amount' => $request->input('amount'),
                'amount_formatted' => $transactions['debit']->getAmountInMajorUnits(),
                'currency' => $sourceWallet->currency,
            ],
        ], 200);
    }
}
