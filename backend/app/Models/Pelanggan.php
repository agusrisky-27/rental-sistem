<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Pelanggan extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pelanggan';


    protected $fillable = [
        'user_id', 'nama', 'email', 'telepon', 'alamat', 'level', 'foto_ktp',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
