<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Transaksi extends Model
{
    use HasFactory;
    protected $table = 'transaksi';

    // Status yang mengunci jadwal kendaraan
    public const STATUS_JADWAL = ['menunggu', 'aktif'];

    protected $fillable = [
        'kendaraan_id', 'pelanggan_id', 'tanggal_mulai',
        'tanggal_akhir', 'total', 'status',
    ];

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class)->withTrashed();
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class)->withTrashed();
    }

    // Pembayaran sewa terbaru (bisa ada beberapa jika bukti sebelumnya ditolak)
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('jenis', 'sewa'));
    }

    public function semuaPembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class);
    }

    // Bentrok jika mulai_lama < akhir_baru dan akhir_lama > mulai_baru (hari kembali boleh dipakai booking berikutnya)
    public function scopeBentrok($query, string $mulai, string $akhir)
    {
        return $query->whereIn('status', self::STATUS_JADWAL)
            ->where('tanggal_mulai', '<', Carbon::parse($akhir)->toDateString())
            ->where('tanggal_akhir', '>', Carbon::parse($mulai)->toDateString());
    }

    public static function buat(int $kendaraanId, int $pelangganId, string $mulai, string $akhir): self
    {
        return DB::transaction(function () use ($kendaraanId, $pelangganId, $mulai, $akhir) {
            $kendaraan = Kendaraan::lockForUpdate()->findOrFail($kendaraanId);

            if ($kendaraan->status === 'maintenance') {
                throw ValidationException::withMessages(['kendaraan_id' => 'Kendaraan sedang dalam perawatan.']);
            }
            if ($kendaraan->transaksi()->bentrok($mulai, $akhir)->exists()) {
                throw ValidationException::withMessages(['tanggal_mulai' => 'Kendaraan sudah dibooking pada tanggal tersebut.']);
            }

            $durasi = (int) Carbon::parse($mulai)->startOfDay()->diffInDays(Carbon::parse($akhir)->startOfDay());

            return self::create([
                'kendaraan_id'  => $kendaraan->id,
                'pelanggan_id'  => $pelangganId,
                'tanggal_mulai' => Carbon::parse($mulai)->toDateString(),
                'tanggal_akhir' => Carbon::parse($akhir)->toDateString(),
                'total'         => $kendaraan->harga * max(1, $durasi),
                'status'        => 'menunggu',
            ]);
        });
    }

    public function pastikanStatus(string ...$status): void
    {
        if (! in_array($this->status, $status, true)) {
            throw ValidationException::withMessages([
                'status' => "Aksi tidak bisa dilakukan pada transaksi berstatus {$this->status}.",
            ]);
        }
    }

    // Dipanggil di dalam DB::transaction milik pemanggil
    public function aktifkan(): void
    {
        $this->pastikanStatus('menunggu');
        $this->update(['status' => 'aktif']);
        $this->kendaraan->update(['status' => 'disewa']);
    }

    public function batalkan(): void
    {
        $this->pastikanStatus('menunggu');

        DB::transaction(function () {
            $this->update(['status' => 'dibatalkan']);
            $this->lepasKendaraan();
        });
    }

    // Kendaraan kembali tersedia hanya jika tidak ada sewa aktif lain; status maintenance tidak disentuh
    public function lepasKendaraan(): void
    {
        $kendaraan = $this->kendaraan;
        if ($kendaraan->status === 'disewa' && ! $kendaraan->transaksi()->where('status', 'aktif')->exists()) {
            $kendaraan->update(['status' => 'tersedia']);
        }
    }

    public function userPelangganId(): ?int
    {
        return $this->pelanggan?->user_id;
    }
}
