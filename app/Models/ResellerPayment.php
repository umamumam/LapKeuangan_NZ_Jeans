<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerPayment extends Model
{
    protected $fillable = [
        'reseller_id',
        'reseller_transaction_id',
        'tgl',
        'nominal',
        'bukti_tf',
        'keterangan'
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function transaction()
    {
        return $this->belongsTo(ResellerTransaction::class, 'reseller_transaction_id');
    }
}
