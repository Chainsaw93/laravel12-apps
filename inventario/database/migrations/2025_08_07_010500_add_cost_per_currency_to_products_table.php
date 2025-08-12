<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_cup', 18, 4)->default(0)->after('currency');
            $table->decimal('cost_usd', 18, 4)->nullable()->after('cost_cup');
            $table->decimal('cost_mlc', 18, 4)->nullable()->after('cost_usd');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cost_cup', 'cost_usd', 'cost_mlc']);
        });
    }
};

