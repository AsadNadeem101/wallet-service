<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create users table migration.
 *
 * This migration creates the users table for storing user information.
 * No authentication fields (password, tokens) are included as per requirements.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the user's table with basic user information.
     * Email is unique to prevent duplicate users.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Full name of the user');
            $table->string('email')->unique()->comment('Unique email address for user identification');
            $table->timestamps();

            // Indexes
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the user's table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
