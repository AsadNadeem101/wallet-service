<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create wallets table migration.
 *
 * This migration creates the wallets table for storing digital wallets.
 * Each wallet has an owner name and holds a balance in a specific currency.
 * Balances are stored as integers in minor units (e.g., cents) to avoid
 * floating-point precision issues.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the wallets table with:
     * - Owner name field
     * - Currency code (ISO 4217 format)
     * - Balance stored in minor units (bigInteger)
     * - Appropriate indexes for performance
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name')
                ->comment('Name of the wallet owner');
            $table->string('currency', 3)
                ->comment('ISO 4217 currency code (USD, EUR, GBP, SAR, etc.)');
            $table->unsignedBigInteger('balance')
                ->default(0)
                ->comment('Wallet balance in minor units (e.g., cents for USD)');
            $table->timestamps();

            // Indexes for performance
            $table->index('owner_name');
            $table->index('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the wallets table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
