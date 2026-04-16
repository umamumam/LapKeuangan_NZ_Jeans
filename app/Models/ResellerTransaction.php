<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerTransaction extends Model
{
    protected $fillable = [
        'reseller_id',
        'tgl',
        'total_barang',
        'total_uang',
        'total_keuntungan',
        'bayar',
        'sisa_kurang',
        'retur',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function details()
    {
        return $this->hasMany(ResellerTransactionDetail::class);
    }
}
