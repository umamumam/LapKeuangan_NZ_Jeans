<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    protected $fillable = [
        'tanggal',
        'jenis_barang',
        'ukuran',
        'harga_satuan',
        'ball',
        'pack',
        'jumlah',
        'retur',
        'status',
        'kurang_bayar',
        'kategori',
    ];
}
