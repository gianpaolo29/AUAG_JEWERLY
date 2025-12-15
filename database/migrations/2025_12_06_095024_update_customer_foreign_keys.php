<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) pawn_items.customer_id → customers.id
        Schema::table('pawn_items', function (Blueprint $table) {
            // Drop existing FK (likely pointing to users.id or old definition)
            $table->dropForeign(['customer_id']);

            // New FK pointing to customers.id
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnUpdate();
        });

        // 2) repairs.customer_id → customers.id
        Schema::table('repairs', function (Blueprint $table) {
            // Drop existing FK
            $table->dropForeign(['customer_id']);

            // New FK pointing to customers.id
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnUpdate();
        });

        // 3) transactions.customer_id → customers.id
        Schema::table('transactions', function (Blueprint $table) {
            // Drop existing FK (was pointing to users.id)
            $table->dropForeign(['customer_id']);

            // New FK pointing to customers.id
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->nullOnDelete()     // ON DELETE SET NULL
                ->cascadeOnUpdate(); // optional but nice to keep consistent
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert pawn_items.customer_id → users.id
        Schema::table('pawn_items', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);

            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate();
        });

        // Revert repairs.customer_id → users.id
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);

            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate();
        });

        // Revert transactions.customer_id → users.id (original behavior)
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);

            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete(); // ON DELETE SET NULL
        });
    }
};
