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
        Schema::table('reseller_transactions', function (Blueprint $table) {
            $table->boolean('is_retur')->default(false)->after('retur');
        });

        Schema::table('supplier_transactions', function (Blueprint $table) {
            $table->boolean('is_retur')->default(false)->after('retur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_transactions', function (Blueprint $table) {
            $table->dropColumn('is_retur');
        });

        Schema::table('supplier_transactions', function (Blueprint $table) {
            $table->dropColumn('is_retur');
        });
    }
};
