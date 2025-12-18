<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\TransactionResource;
use App\Models\Wallet;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Wallet Controller
 *
 * Handles HTTP requests for wallet operations including:
 * - Creating wallets
 * - Retrieving wallet information
 * - Listing wallets with filters
 * - Depositing funds
 * - Withdrawing funds
 * - Getting wallet balance
 */
class WalletController extends Controller
{
    /**
     * Wallet service instance.
     *
     * @var WalletService
     */
    protected WalletService $walletService;

    /**
     * Constructor - Dependency Injection.
     *
     * @param WalletService $walletService
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Create a new wallet.
     *
     * POST /api/wallets
     *
     * @param StoreWalletRequest $request
     * @return JsonResponse
     */
    public function store(StoreWalletRequest $request): JsonResponse
    {
        try {
            $wallet = $this->walletService->createWallet(
                $request->input('owner_name'),
                $request->input('currency')
            );

            return response()->json([
                'success' => true,
                'message' => 'Wallet created successfully.',
                'data' => new WalletResource($wallet),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create wallet.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get wallet details.
     *
     * GET /api/wallets/{wallet}
     *
     * @param  Wallet  $wallet
     * @return JsonResponse
     */
    public function show(Wallet $wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Wallet retrieved successfully.',
            'data' => new WalletResource($wallet),
        ], 200);
    }

    /**
     * List all wallets with optional filters.
     *
     * GET /api/wallets?owner=John&currency=USD
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['owner', 'currency']);

        $wallets = $this->walletService->listWallets($filters);

        return response()->json([
            'success' => true,
            'message' => 'Wallets retrieved successfully.',
            'data' => WalletResource::collection($wallets),
            'count' => $wallets->count(),
        ], 200);
    }

    /**
     * Deposit funds into a wallet.
     *
     * POST /api/wallets/{wallet}/deposit
     *
     * @param  DepositRequest  $request
     * @param  Wallet  $wallet
     * @return JsonResponse
     * @throws Exception
     */
    public function deposit(DepositRequest $request, Wallet $wallet): JsonResponse
    {
        $transaction = $this->walletService->deposit(
            $wallet,
            $request->input('amount'),
            $request->input('idempotency_key')
        );

        return response()->json([
            'success' => true,
            'message' => 'Deposit successful.',
            'data' => new TransactionResource($transaction),
        ], 200);
    }

    /**
     * Withdraw funds from a wallet.
     *
     * POST /api/wallets/{wallet}/withdraw
     *
     * @param  WithdrawRequest  $request
     * @param  Wallet  $wallet
     * @return JsonResponse
     * @throws Exception
     */
    public function withdraw(WithdrawRequest $request, Wallet $wallet): JsonResponse
    {
        $transaction = $this->walletService->withdraw(
            $wallet,
            $request->input('amount'),
            $request->input('idempotency_key')
        );

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal successful.',
            'data' => new TransactionResource($transaction),
        ], 200);
    }

    /**
     * Get wallet balance.
     *
     * GET /api/wallets/{wallet}/balance
     *
     * @param  Wallet  $wallet
     * @return JsonResponse
     */
    public function balance(Wallet $wallet): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Balance retrieved successfully.',
            'data' => new WalletResource($wallet),
        ], 200);
    }
}
