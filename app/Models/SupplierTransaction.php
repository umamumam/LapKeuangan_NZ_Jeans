<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierTransaction extends Model
{
    protected $fillable = [
        'supplier_id',
        'tgl',
        'total_barang',
        'total_uang',
        'retur',
        'bayar',
        'total_tagihan',
        'bukti_tf',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(SupplierTransactionDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
