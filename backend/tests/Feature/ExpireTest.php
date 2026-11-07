<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuatData;
use Tests\TestCase;

class ExpireTest extends TestCase
{
    use RefreshDatabase, BuatData;

    private function transaksiUmur(int $jam): Transaksi
    {
        $transaksi = $this->buatTransaksi($this->buatKendaraan(), $this->buatPelangganUnik(), 1, 3);
        $transaksi->forceFill(['created_at' => now()->subHours($jam)])->save();

        return $transaksi;
    }

    private function buatPelangganUnik()
    {
        static $n = 0;
        return $this->buatPelanggan('p' . ++$n . '@test.com');
    }

    public function test_menunggu_lebih_24_jam_tanpa_pembayaran_dibatalkan(): void
    {
        $transaksi = $this->transaksiUmur(25);

        $this->artisan('transaksi:expire')->assertSuccessful();

        $this->assertSame('dibatalkan', $transaksi->fresh()->status);
    }

    public function test_menunggu_lebih_24_jam_dengan_pembayaran_menunggu_verifikasi_tetap(): void
    {
        $transaksi = $this->transaksiUmur(25);
        Pembayaran::create([
            'transaksi_id' => $transaksi->id, 'jenis' => 'sewa', 'metode' => 'Transfer Bank',
            'jumlah' => $transaksi->total, 'status' => 'menunggu verifikasi',
        ]);

        $this->artisan('transaksi:expire')->assertSuccessful();

        $this->assertSame('menunggu', $transaksi->fresh()->status);
    }

    public function test_menunggu_kurang_dari_24_jam_tetap(): void
    {
        $transaksi = $this->transaksiUmur(2);

        $this->artisan('transaksi:expire')->assertSuccessful();

        $this->assertSame('menunggu', $transaksi->fresh()->status);
    }
}
