<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('currency', 3)->default('CUP')->after('purchase_price');
            $table->foreignId('exchange_rate_id')->nullable()->after('currency')->constrained()->nullOnDelete();
            $table->text('description')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exchange_rate_id');
            $table->dropColumn(['currency', 'description']);
        });
    }
};
