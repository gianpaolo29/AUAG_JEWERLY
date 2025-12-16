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
        Schema::create('gold_forecasts', function (Blueprint $table) {
            $table->id();
            $table->date('as_of_date');
            $table->date('target_date');
            $table->decimal('predicted_usd', 14, 4);
            $table->decimal('lower_usd', 14, 4)->nullable();
            $table->decimal('upper_usd', 14, 4)->nullable();
            $table->foreignId('gold_model_run_id')->constrained('gold_model_runs');
            $table->timestamps();

            $table->unique(['as_of_date', 'target_date', 'gold_model_run_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_forecasts');
    }
};
