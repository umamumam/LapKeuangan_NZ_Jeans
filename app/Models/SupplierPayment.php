<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = [
        'supplier_id',
        'supplier_transaction_id',
        'tgl',
        'nominal',
        'bukti_tf',
        'keterangan'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transaction()
    {
        return $this->belongsTo(SupplierTransaction::class, 'supplier_transaction_id');
    }
}
