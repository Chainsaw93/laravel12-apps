<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->enum('movement_type', ['in', 'out', 'transfer_in', 'transfer_out', 'adjustment_pos', 'adjustment_neg']);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost_cup', 18, 4);
            $table->decimal('indirect_cost_unit', 18, 4)->default(0);
            $table->string('currency', 3)->default('CUP');
            $table->foreignId('exchange_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_cost_cup', 18, 4);
            $table->nullableMorphs('reference');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
