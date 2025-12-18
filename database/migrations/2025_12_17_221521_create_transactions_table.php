<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create transactions table migration.
 *
 * This migration creates the transactions table implementing double-entry accounting.
 * Each transaction records a single entry (debit or credit) for a wallet.
 * Transfers create two transaction records (one debit, one credit) linked together.
 *
 * Features:
 * - Idempotency support via idempotency_key
 * - Balance snapshots for audit trail
 * - Support for deposits, withdrawals, and transfers
 * - Metadata storage for additional context.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the transactions table with:
     * - Foreign key to wallets
     * - Transaction type enum
     * - Amount in minor units
     * - Balance snapshot after transaction
     * - Idempotency key for duplicate prevention
     * - Related wallet reference for transfers
     * - Flexible metadata storage
     * - Performance indexes
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->onDelete('cascade')
                ->comment('Reference to the wallet this transaction belongs to');
            $table->enum('type', ['deposit', 'withdrawal', 'transfer_debit', 'transfer_credit'])
                ->comment('Type of transaction: deposit, withdrawal, or transfer (debit/credit)');
            $table->unsignedBigInteger('amount')
                ->comment('Transaction amount in minor units (always positive)');
            $table->unsignedBigInteger('balance_after')
                ->comment('Wallet balance snapshot after this transaction (minor units)');
            $table->foreignId('related_wallet_id')
                ->nullable()
                ->constrained('wallets')
                ->onDelete('set null')
                ->comment('For transfers: reference to the other wallet involved');
            $table->string('idempotency_key', 64)
                ->nullable()
                ->comment('Unique key to prevent duplicate operations');
            $table->json('metadata')
                ->nullable()
                ->comment('Additional transaction data (description, references, etc.)');
            $table->timestamps();

            // Indexes for performance and queries
            $table->index('wallet_id');
            $table->index('idempotency_key');
            $table->index('type');
            $table->index('created_at');
            $table->index(['wallet_id', 'created_at']);
            $table->index(['wallet_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the transactions table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
