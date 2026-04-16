<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierTransactionDetail extends Model
{
    protected $fillable = [
        'supplier_transaction_id',
        'barang_id',
        'jumlah',
        'subtotal',
    ];

    public function transaction()
    {
        return $this->belongsTo(SupplierTransaction::class, 'supplier_transaction_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
