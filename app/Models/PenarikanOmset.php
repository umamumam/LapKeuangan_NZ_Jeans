<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenarikanOmset extends Model
{
    protected $fillable = ['toko_id', 'tgl', 'jumlah'];

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }
}
