<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Kendaraan extends Model
{
    use HasFactory;
    protected $table = 'kendaraan';

    protected $fillable = [
        'nama', 'tipe', 'plat', 'kapasitas', 'harga',
        'tahun', 'warna', 'deskripsi', 'status', 'foto',
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute()
    {
        return $this->foto ? url(Storage::url($this->foto)) : null;
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
