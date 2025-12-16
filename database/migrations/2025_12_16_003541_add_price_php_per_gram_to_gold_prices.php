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
        Schema::table('gold_prices', function (Blueprint $table) {
            $table->decimal('usd_to_php', 12, 6)->nullable()->after('price_usd');
            $table->decimal('price_php_per_gram', 14, 4)->nullable()->after('usd_to_php');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gold_prices', function (Blueprint $table) {
            $table->dropColumn('usd_to_php');
            $table->dropColumn('price_php_per_gram');
        });
    }
};
