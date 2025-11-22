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
        Schema::create('transaction_items', function (Blueprint $table) {
            
            $table->id(); // transaction_item_id
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();

            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pawn_item_id')->nullable()->constrained('pawn_items')->nullOnDelete();
            $table->foreignId('repair_id')->nullable()->constrained('repairs')->nullOnDelete();

            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0); 
            $table->decimal('line_total', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['transaction_id']);
            $table->index(['product_id']);
            $table->index(['pawn_item_id']);
            $table->index(['repair_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
