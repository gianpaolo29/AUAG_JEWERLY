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
        Schema::create('pawn_items', function (Blueprint $table) {
            $table->id(); // pawn_id
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('price', 12, 2)->default(0);          // principal / appraised value
            $table->decimal('interest_cost', 12, 2)->default(0);  // interest amount
            $table->date('due_date')->nullable();
            $table->enum('status', ['active','redeemed','forfeited'])->default('active'); // helpful
            $table->timestamps();

            $table->index(['customer_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pawn_items');
    }
};
