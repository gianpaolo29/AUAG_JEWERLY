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
        Schema::create('gold_model_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('trained_at');
            $table->string('model_path');
            $table->unsignedInteger('lookback');
            $table->json('metrics')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_model_runs');
    }
};
