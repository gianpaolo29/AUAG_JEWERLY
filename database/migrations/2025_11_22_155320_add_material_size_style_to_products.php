<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->enum('material', [
                'gold',
                'silver',
                'stainless',
                'diamond',
                'pearl',
                'gemstone',
                'other'
            ])->nullable()->after('quantity');

            $table->string('size')->nullable()->after('material');

            $table->enum('style', [
                'minimalist',
                'vintage',
                'classic',
                'modern',
                'luxury',
                'casual',
                'other'
            ])->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['material', 'size', 'style']);
        });
    }
};
