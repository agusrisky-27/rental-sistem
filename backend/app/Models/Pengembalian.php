<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Pengembalian extends Model
{
    use HasFactory;
    protected $table = 'pengembalian';


    protected $fillable = [
        'transaksi_id', 'tanggal_kembali', 'kondisi_kendaraan', 'denda', 'status', 'denda_status',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    // Satu-satunya jalur pengembalian: hitung denda, selesaikan transaksi, lepas kendaraan
    public static function catat(Transaksi $transaksi, string $tanggalKembali, ?string $kondisi): self
    {
        $transaksi->pastikanStatus('aktif');

        $kembali = Carbon::parse($tanggalKembali)->startOfDay();
        if ($kembali->lt(Carbon::parse($transaksi->tanggal_mulai)->startOfDay())) {
            throw ValidationException::withMessages(['tanggal_kembali' => 'Tanggal kembali tidak boleh sebelum tanggal mulai sewa.']);
        }

        // Carbon 3: diffInDays bertanda, positif jika $kembali setelah tanggal_akhir
        $telat = max(0, (int) Carbon::parse($transaksi->tanggal_akhir)->startOfDay()->diffInDays($kembali));
        $denda = $transaksi->kendaraan->harga * $telat;

        $pengembalian = DB::transaction(function () use ($transaksi, $kembali, $kondisi, $denda) {
            $pengembalian = self::create([
                'transaksi_id'      => $transaksi->id,
                'tanggal_kembali'   => $kembali->toDateString(),
                'kondisi_kendaraan' => $kondisi,
                'denda'             => $denda,
                'status'            => $denda > 0 ? 'terlambat' : 'tepat waktu',
                'denda_status'      => $denda > 0 ? 'belum bayar' : 'tidak ada',
            ]);

            $transaksi->update(['status' => 'selesai']);
            $transaksi->lepasKendaraan();

            return $pengembalian;
        });

        if ($denda > 0) {
            Notification::keUser($transaksi->userPelangganId(), 'payment', 'Denda keterlambatan',
                "Pengembalian transaksi #{$transaksi->id} terlambat {$telat} hari. Denda Rp " . number_format($denda, 0, ',', '.') . '.',
                ['transaksi_id' => $transaksi->id]);
        }

        return $pengembalian;
    }

    // Denda dibayar tunai di tempat
    public function lunasi(): void
    {
        if ($this->denda_status !== 'belum bayar') {
            throw ValidationException::withMessages(['denda_status' => 'Tidak ada denda yang perlu dilunasi.']);
        }
        if ($this->transaksi->semuaPembayaran()->where('jenis', 'denda')->where('status', 'menunggu verifikasi')->exists()) {
            throw ValidationException::withMessages(['denda_status' => 'Ada bukti pembayaran denda yang menunggu verifikasi.']);
        }

        DB::transaction(function () {
            Pembayaran::create([
                'transaksi_id'  => $this->transaksi_id,
                'jenis'         => 'denda',
                'metode'        => 'tunai',
                'jumlah'        => $this->denda,
                'status'        => 'berhasil',
                'tanggal_bayar' => now(),
            ]);
            $this->update(['denda_status' => 'lunas']);
        });
    }
}
