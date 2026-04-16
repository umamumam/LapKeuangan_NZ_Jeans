<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reseller_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('barang_id')->constrained()->onDelete('cascade');
            $table->integer('jumlah')->default(0);
            $table->integer('subtotal')->default(0);
            $table->integer('keuntungan')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reseller_transaction_details');
    }
};
