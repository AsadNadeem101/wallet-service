<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/**
 * Wallet Service API Routes
 *
 * RESTful API endpoints for wallet management, transactions, and transfers.
 * No authentication required as per requirements.
 */

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

/*
|--------------------------------------------------------------------------
| Wallet Management
|--------------------------------------------------------------------------
*/
Route::prefix('wallets')->group(function () {
    // Wallet CRUD operations
    Route::controller(WalletController::class)->group(function () {
        Route::post('/', 'store');
        Route::get('/', 'index');
        Route::get('{wallet}', 'show');
        Route::get('{wallet}/balance', 'balance');
        Route::post('{wallet}/deposit', 'deposit');
        Route::post('{wallet}/withdraw', 'withdraw');
    });

    // Transaction history
    Route::get('{wallet}/transactions', [TransactionController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Transfers
|--------------------------------------------------------------------------
*/
Route::post('/transfers', [TransferController::class, 'store']);
