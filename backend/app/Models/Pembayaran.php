<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'pembayaran';


    protected $fillable = [
        'transaksi_id', 'metode', 'jumlah', 'status', 'bukti', 'tanggal_bayar',
    ];

    protected $appends = ['bukti_url'];

    public function getBuktiUrlAttribute()
    {
        return $this->bukti ? url(Storage::url($this->bukti)) : null;
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
