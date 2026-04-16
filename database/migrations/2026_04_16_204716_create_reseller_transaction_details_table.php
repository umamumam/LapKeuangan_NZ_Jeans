<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reseller_transaction_details', function (Blueprint $バランス) {
            $バランス->id();
            $バランス->foreignId('reseller_transaction_id')->constrained()->onDelete('cascade');
            $バランス->foreignId('barang_id')->constrained()->onDelete('cascade');
            $バランス->integer('jumlah')->default(0);
            $バランス->integer('subtotal')->default(0);
            $バランス->integer('keuntungan')->default(0);
            $バランス->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reseller_transaction_details');
    }
};
