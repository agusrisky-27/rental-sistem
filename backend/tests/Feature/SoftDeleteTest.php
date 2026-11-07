<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuatData;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase, BuatData;

    public function test_hapus_kendaraan_dengan_transaksi_aktif_ditolak_422(): void
    {
        $kendaraan = $this->buatKendaraan(['status' => 'disewa']);
        $this->buatTransaksi($kendaraan, $this->buatPelanggan(), -1, 2, 'aktif');
        Sanctum::actingAs($this->buatAdmin());

        $this->deleteJson("/api/kendaraan/{$kendaraan->id}")->assertStatus(422);

        $this->assertNotNull(Kendaraan::find($kendaraan->id));
    }

    public function test_hapus_kendaraan_tanpa_transaksi_aktif_soft_delete_dan_riwayat_tetap_memuat_kendaraan(): void
    {
        $kendaraan = $this->buatKendaraan(['nama' => 'Xenia Lama']);
        $transaksi = $this->buatTransaksi($kendaraan, $this->buatPelanggan(), -5, -2, 'selesai');
        Sanctum::actingAs($this->buatAdmin());

        $this->deleteJson("/api/kendaraan/{$kendaraan->id}")->assertOk();

        $this->assertSoftDeleted('kendaraan', ['id' => $kendaraan->id]);
        $this->getJson("/api/transaksi/{$transaksi->id}")
            ->assertOk()
            ->assertJsonPath('kendaraan.nama', 'Xenia Lama');
    }
}
