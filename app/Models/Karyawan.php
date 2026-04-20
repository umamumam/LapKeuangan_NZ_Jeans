<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $fillable = ['nama', 'jabatan'];

    public function penggajians()
    {
        return $this->hasMany(Penggajian::class);
    }
}
