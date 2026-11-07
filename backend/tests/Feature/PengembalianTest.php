<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pengembalian;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuatData;
use Tests\TestCase;

class PengembalianTest extends TestCase
{
    use RefreshDatabase, BuatData;

    private Pelanggan $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pelanggan = $this->buatPelanggan();
    }

    // Transaksi aktif; kendaraan berstatus disewa
    private function aktif(int $mulai, int $akhir): Transaksi
    {
        $kendaraan = $this->buatKendaraan(['harga' => 500000, 'status' => 'disewa']);

        return $this->buatTransaksi($kendaraan, $this->pelanggan, $mulai, $akhir, 'aktif');
    }

    private function selesaikan(Transaksi $transaksi, int $hariKembali = 0)
    {
        Sanctum::actingAs($this->buatAdmin());

        return $this->patchJson("/api/transaksi/{$transaksi->id}/selesai", [
            'tanggal_kembali' => now()->addDays($hariKembali)->toDateString(),
        ]);
    }

    public function test_pengembalian_tepat_waktu_tanpa_denda(): void
    {
        $transaksi = $this->aktif(-3, 1);

        $this->selesaikan($transaksi)->assertOk();

        $this->assertDatabaseHas('pengembalian', [
            'transaksi_id' => $transaksi->id, 'denda' => 0, 'status' => 'tepat waktu', 'denda_status' => 'tidak ada',
        ]);
        $this->assertSame('selesai', $transaksi->fresh()->status);
        $this->assertSame('tersedia', $transaksi->kendaraan->fresh()->status);
    }

    public function test_pengembalian_telat_dua_hari_dikenai_denda(): void
    {
        $transaksi = $this->aktif(-5, -2);

        $this->selesaikan($transaksi)->assertOk();

        $pengembalian = Pengembalian::firstOrFail();
        $this->assertEquals(1000000, $pengembalian->denda);
        $this->assertSame('terlambat', $pengembalian->status);
        $this->assertSame('belum bayar', $pengembalian->denda_status);
        $this->assertSame('selesai', $transaksi->fresh()->status);
    }

    public function test_pengembalian_transaksi_menunggu_ditolak_422(): void
    {
        $transaksi = $this->buatTransaksi($this->buatKendaraan(), $this->pelanggan, 1, 3);
        Sanctum::actingAs($this->buatAdmin());

        $this->postJson('/api/pengembalian', [
            'transaksi_id' => $transaksi->id, 'tanggal_kembali' => now()->toDateString(),
        ])->assertStatus(422);
    }

    public function test_lunas_membuat_pembayaran_denda_berhasil_dan_menandai_lunas(): void
    {
        $transaksi = $this->aktif(-5, -2);
        $this->selesaikan($transaksi)->assertOk();
        $pengembalian = Pengembalian::firstOrFail();

        $this->patchJson("/api/pengembalian/{$pengembalian->id}/lunas")->assertOk();

        $this->assertSame('lunas', $pengembalian->fresh()->denda_status);
        $pembayaran = Pembayaran::where('transaksi_id', $transaksi->id)->where('jenis', 'denda')->firstOrFail();
        $this->assertSame('berhasil', $pembayaran->status);
        $this->assertSame('tunai', $pembayaran->metode);
        $this->assertEquals(1000000, $pembayaran->jumlah);
    }

    public function test_pelanggan_upload_bukti_denda_jumlah_sama_dengan_denda(): void
    {
        Storage::fake('public');
        $transaksi = $this->aktif(-5, -2);
        $this->selesaikan($transaksi)->assertOk();

        Sanctum::actingAs($this->pelanggan->user);
        $this->post("/api/akun/transaksi/{$transaksi->id}/pembayaran", [
            'jenis' => 'denda', 'metode' => 'QRIS', 'jumlah' => 1, 'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $pembayaran = Pembayaran::where('jenis', 'denda')->firstOrFail();
        $this->assertEquals(Pengembalian::firstOrFail()->denda, $pembayaran->jumlah);
        $this->assertSame('menunggu verifikasi', $pembayaran->status);
    }
}
