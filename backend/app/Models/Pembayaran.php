<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaksi_id', 'metode', 'jumlah', 'status', 'bukti', 'tanggal_bayar',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
