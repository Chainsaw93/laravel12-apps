<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('user_id')->after('exchange_rate_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('user_id')->after('exchange_rate_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
