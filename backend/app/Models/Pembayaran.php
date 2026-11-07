<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'pembayaran';


    protected $fillable = [
        'transaksi_id', 'jenis', 'metode', 'jumlah', 'status', 'bukti', 'tanggal_bayar', 'catatan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
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

    // Jumlah selalu dihitung server: sewa = total transaksi, denda = denda pengembalian
    public static function ajukan(Transaksi $transaksi, string $jenis, string $metode, ?UploadedFile $bukti): self
    {
        if ($jenis === 'sewa') {
            $transaksi->pastikanStatus('menunggu');
            $jumlah = $transaksi->total;
        } else {
            $pengembalian = $transaksi->pengembalian;
            if (! $pengembalian || $pengembalian->denda_status !== 'belum bayar') {
                throw ValidationException::withMessages(['jenis' => 'Tidak ada denda yang perlu dibayar.']);
            }
            $jumlah = $pengembalian->denda;
        }

        $masihProses = $transaksi->semuaPembayaran()
            ->where('jenis', $jenis)
            ->whereIn('status', ['menunggu verifikasi', 'berhasil'])
            ->exists();
        if ($masihProses) {
            throw ValidationException::withMessages(['bukti' => 'Pembayaran sedang diverifikasi atau sudah lunas.']);
        }

        $pembayaran = self::create([
            'transaksi_id' => $transaksi->id,
            'jenis'        => $jenis,
            'metode'       => $metode,
            'jumlah'       => $jumlah,
            'status'       => 'menunggu verifikasi',
            'bukti'        => $bukti?->store('bukti_bayar', 'public'),
        ]);

        Notification::keAdmin('payment', 'Bukti pembayaran baru',
            "Pembayaran {$jenis} untuk transaksi #{$transaksi->id} menunggu verifikasi.",
            ['transaksi_id' => $transaksi->id, 'pembayaran_id' => $pembayaran->id]);

        return $pembayaran;
    }

    public function verifikasi(): void
    {
        $this->pastikanMenunggu();

        DB::transaction(function () {
            $this->update(['status' => 'berhasil', 'tanggal_bayar' => now()]);

            if ($this->jenis === 'sewa') {
                $this->transaksi->aktifkan();
            } else {
                $this->transaksi->pengembalian->update(['denda_status' => 'lunas']);
            }
        });

        Notification::keUser($this->transaksi->userPelangganId(), 'payment', 'Pembayaran diverifikasi',
            "Pembayaran {$this->jenis} untuk transaksi #{$this->transaksi_id} telah diterima.",
            ['transaksi_id' => $this->transaksi_id]);
    }

    public function tolak(?string $catatan): void
    {
        $this->pastikanMenunggu();
        $this->update(['status' => 'ditolak', 'catatan' => $catatan]);

        Notification::keUser($this->transaksi->userPelangganId(), 'payment', 'Pembayaran ditolak',
            "Bukti pembayaran transaksi #{$this->transaksi_id} ditolak" . ($catatan ? ": {$catatan}" : '.') . ' Silakan upload ulang.',
            ['transaksi_id' => $this->transaksi_id]);
    }

    private function pastikanMenunggu(): void
    {
        if ($this->status !== 'menunggu verifikasi') {
            throw ValidationException::withMessages(['status' => "Pembayaran sudah berstatus {$this->status}."]);
        }
    }
}
