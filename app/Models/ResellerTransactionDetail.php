<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerTransactionDetail extends Model
{
    protected $fillable = [
        'reseller_transaction_id',
        'barang_id',
        'jumlah',
        'subtotal',
        'keuntungan',
    ];

    public function transaction()
    {
        return $this->belongsTo(ResellerTransaction::class, 'reseller_transaction_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
