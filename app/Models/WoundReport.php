<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WoundReport extends Model
{
    protected $fillable = [
        'tanggal',
        'pengerjaan',
        'jenis_produk',
        'shift',
        'hasil',
        'produk_yang_dikerjakan',
        'satuan',
        'keterangan',
        'vendor',
        'operator',
    ];
}
