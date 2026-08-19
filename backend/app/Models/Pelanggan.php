<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pelanggan extends Model
{
    use HasFactory;
    protected $table = 'pelanggan';


    protected $fillable = [
        'nama', 'email', 'telepon', 'alamat', 'level', 'foto_ktp',
    ];

    protected $appends = ['foto_ktp_url'];

    public function getFotoKtpUrlAttribute()
    {
        return $this->foto_ktp ? url(Storage::url($this->foto_ktp)) : null;
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
