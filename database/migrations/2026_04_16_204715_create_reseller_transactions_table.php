<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reseller_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained()->onDelete('cascade');
            $table->date('tgl');
            $table->integer('total_barang')->default(0);
            $table->integer('total_uang')->default(0);
            $table->integer('total_keuntungan')->default(0);
            $table->integer('bayar')->default(0);
            $table->integer('sisa_kurang')->default(0);
            $table->integer('retur')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reseller_transactions');
    }
};
