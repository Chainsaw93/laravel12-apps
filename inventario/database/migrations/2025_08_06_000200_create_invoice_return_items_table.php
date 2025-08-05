<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_return_id')->constrained('invoice_returns')->cascadeOnDelete();
            $table->foreignId('invoice_item_id')->constrained('invoice_items')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('amount', 12, 2);
            $table->decimal('cost', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_return_items');
    }
};
