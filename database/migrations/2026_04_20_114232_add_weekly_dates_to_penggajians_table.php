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
        Schema::table('penggajians', function (Blueprint $table) {
            $table->date('tgl_m1')->nullable()->after('tanggal');
            $table->date('tgl_m2')->nullable()->after('tgl_m1');
            $table->date('tgl_m3')->nullable()->after('tgl_m2');
            $table->date('tgl_m4')->nullable()->after('tgl_m3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->dropColumn(['tgl_m1', 'tgl_m2', 'tgl_m3', 'tgl_m4']);
        });
    }
};
