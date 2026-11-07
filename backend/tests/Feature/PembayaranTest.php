<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuatData;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase, BuatData;

    private Pelanggan $pelanggan;
    private Transaksi $transaksi;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->pelanggan = $this->buatPelanggan();
        $this->transaksi = $this->buatTransaksi($this->buatKendaraan(['harga' => 500000]), $this->pelanggan, 1, 3);
    }

    // Upload bukti sebagai pelanggan pemilik transaksi
    private function unggah(array $extra = [])
    {
        Sanctum::actingAs($this->pelanggan->user);

        return $this->post("/api/akun/transaksi/{$this->transaksi->id}/pembayaran", [
            'jenis' => 'sewa', 'metode' => 'Transfer Bank', 'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ] + $extra, ['Accept' => 'application/json']);
    }

    public function test_upload_bukti_jumlah_dihitung_server_bukan_dari_request(): void
    {
        $this->unggah(['jumlah' => 1])->assertCreated();

        $pembayaran = Pembayaran::firstOrFail();
        $this->assertEquals($this->transaksi->total, $pembayaran->jumlah);
        $this->assertSame('menunggu verifikasi', $pembayaran->status);
        $this->assertSame('sewa', $pembayaran->jenis);
    }

    public function test_upload_kedua_saat_masih_pending_ditolak_422(): void
    {
        $this->unggah()->assertCreated();
        $this->unggah()->assertStatus(422);
    }

    public function test_verifikasi_mengaktifkan_transaksi_menyewakan_kendaraan_dan_notifikasi_pelanggan(): void
    {
        $this->unggah()->assertCreated();
        $pembayaran = Pembayaran::firstOrFail();

        Sanctum::actingAs($this->buatAdmin());
        $this->patchJson("/api/pembayaran/{$pembayaran->id}/verifikasi")->assertOk();

        $this->assertSame('berhasil', $pembayaran->fresh()->status);
        $this->assertSame('aktif', $this->transaksi->fresh()->status);
        $this->assertSame('disewa', $this->transaksi->kendaraan->fresh()->status);
        $this->assertTrue(Notification::where('user_id', $this->pelanggan->user_id)->where('type', 'payment')->exists());

        // verifikasi ulang
        $this->patchJson("/api/pembayaran/{$pembayaran->id}/verifikasi")->assertStatus(422);
    }

    public function test_tolak_dengan_catatan_lalu_pelanggan_bisa_upload_lagi(): void
    {
        $this->unggah()->assertCreated();
        $pembayaran = Pembayaran::firstOrFail();

        Sanctum::actingAs($this->buatAdmin());
        $this->patchJson("/api/pembayaran/{$pembayaran->id}", ['status' => 'ditolak', 'catatan' => 'Bukti buram'])->assertOk();

        $this->assertSame('ditolak', $pembayaran->fresh()->status);
        $this->assertSame('Bukti buram', $pembayaran->fresh()->catatan);

        $this->unggah()->assertCreated();
        $this->assertSame(2, Pembayaran::count());
    }

    public function test_admin_konfirmasi_membuat_pembayaran_tunai_berhasil(): void
    {
        Sanctum::actingAs($this->buatAdmin());

        $this->patchJson("/api/transaksi/{$this->transaksi->id}/konfirmasi")->assertOk();

        $this->assertDatabaseHas('pembayaran', [
            'transaksi_id' => $this->transaksi->id, 'jenis' => 'sewa', 'metode' => 'tunai', 'status' => 'berhasil',
        ]);
        $this->assertSame('aktif', $this->transaksi->fresh()->status);
    }
}
