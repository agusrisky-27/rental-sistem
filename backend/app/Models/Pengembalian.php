<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    use HasFactory;
    protected $table = 'pengembalian';


    protected $fillable = [
        'transaksi_id', 'tanggal_kembali', 'kondisi_kendaraan', 'denda', 'status',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
