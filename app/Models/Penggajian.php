<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penggajian extends Model
{
    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'tgl_m1',
        'tgl_m2',
        'tgl_m3',
        'tgl_m4',
        'minggu_1',
        'minggu_2',
        'minggu_3',
        'minggu_4',
        'nominal'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
