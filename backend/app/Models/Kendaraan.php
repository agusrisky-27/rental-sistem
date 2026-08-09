<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';

    protected $fillable = [
        'nama', 'tipe', 'plat', 'kapasitas', 'harga',
        'tahun', 'warna', 'deskripsi', 'status', 'foto',
    ];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
