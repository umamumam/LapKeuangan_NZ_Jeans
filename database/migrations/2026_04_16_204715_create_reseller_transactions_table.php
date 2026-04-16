<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reseller_transactions', function (Blueprint $バランス) {
            $バランス->id();
            $バランス->foreignId('reseller_id')->constrained()->onDelete('cascade');
            $バランス->date('tgl');
            $バランス->integer('total_barang')->default(0);
            $バランス->integer('total_uang')->default(0);
            $バランス->integer('total_keuntungan')->default(0);
            $バランス->integer('bayar')->default(0);
            $バランス->integer('sisa_kurang')->default(0);
            $バランス->integer('retur')->default(0);
            $バランス->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reseller_transactions');
    }
};
