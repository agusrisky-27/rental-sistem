<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuatData;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase, BuatData;

    private function hari(int $n): string
    {
        return now()->addDays($n)->toDateString();
    }

    private function booking(int $kendaraanId, int $mulai, int $akhir)
    {
        return $this->postJson('/api/akun/transaksi', [
            'kendaraan_id' => $kendaraanId, 'tanggal_mulai' => $this->hari($mulai), 'tanggal_akhir' => $this->hari($akhir),
        ]);
    }

    public function test_booking_sukses_total_harga_kali_hari_dan_kendaraan_tetap_tersedia(): void
    {
        $pelanggan = $this->buatPelanggan();
        $kendaraan = $this->buatKendaraan(['harga' => 500000]);
        Sanctum::actingAs($pelanggan->user);

        $this->booking($kendaraan->id, 1, 4)
            ->assertCreated()
            ->assertJsonPath('status', 'menunggu');

        $this->assertDatabaseHas('transaksi', ['kendaraan_id' => $kendaraan->id, 'pelanggan_id' => $pelanggan->id, 'total' => 1500000]);
        $this->assertSame('tersedia', $kendaraan->fresh()->status);
    }

    public function test_booking_bentrok_ditolak_422(): void
    {
        $pelanggan = $this->buatPelanggan();
        $kendaraan = $this->buatKendaraan();
        $this->buatTransaksi($kendaraan, $pelanggan, 2, 5);
        Sanctum::actingAs($pelanggan->user);

        $this->booking($kendaraan->id, 3, 6)->assertStatus(422);
    }

    public function test_booking_back_to_back_diizinkan(): void
    {
        $pelanggan = $this->buatPelanggan();
        $kendaraan = $this->buatKendaraan();
        $this->buatTransaksi($kendaraan, $pelanggan, 1, 3);
        Sanctum::actingAs($pelanggan->user);

        // mulai = akhir booking lama
        $this->booking($kendaraan->id, 3, 5)->assertCreated();
    }

    public function test_booking_kendaraan_maintenance_ditolak_422(): void
    {
        $pelanggan = $this->buatPelanggan();
        $kendaraan = $this->buatKendaraan(['status' => 'maintenance']);
        Sanctum::actingAs($pelanggan->user);

        $this->booking($kendaraan->id, 1, 3)->assertStatus(422);
    }

    public function test_booking_tanggal_lampau_ditolak_422(): void
    {
        $pelanggan = $this->buatPelanggan();
        $kendaraan = $this->buatKendaraan();
        Sanctum::actingAs($pelanggan->user);

        $this->booking($kendaraan->id, -2, 1)->assertStatus(422);
    }

    public function test_pelanggan_bisa_membatalkan_booking_menunggu(): void
    {
        $pelanggan = $this->buatPelanggan();
        $transaksi = $this->buatTransaksi($this->buatKendaraan(), $pelanggan, 1, 3);
        Sanctum::actingAs($pelanggan->user);

        $this->patchJson("/api/akun/transaksi/{$transaksi->id}/batal")->assertOk();

        $this->assertSame('dibatalkan', $transaksi->fresh()->status);
    }

    public function test_pelanggan_tidak_bisa_melihat_transaksi_orang_lain(): void
    {
        $pemilik = $this->buatPelanggan('pemilik@test.com');
        $lain    = $this->buatPelanggan('lain@test.com');
        $transaksi = $this->buatTransaksi($this->buatKendaraan(), $pemilik, 1, 3);
        Sanctum::actingAs($lain->user);

        $this->getJson("/api/akun/transaksi/{$transaksi->id}")->assertForbidden();
    }

    public function test_katalog_menyaring_kendaraan_bentrok_saat_tanggal_diisi(): void
    {
        $pelanggan = $this->buatPelanggan();
        $dibooking = $this->buatKendaraan(['nama' => 'Dibooking']);
        $kosong    = $this->buatKendaraan(['nama' => 'Kosong']);
        $this->buatTransaksi($dibooking, $pelanggan, 2, 5);

        $res = $this->getJson('/api/katalog?tanggal_mulai=' . $this->hari(3) . '&tanggal_akhir=' . $this->hari(4))->assertOk();

        $this->assertSame([$kosong->id], collect($res->json('data'))->pluck('id')->all());
        // tanpa tanggal, keduanya muncul
        $this->getJson('/api/katalog')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_katalog_detail_memuat_jadwal(): void
    {
        $pelanggan = $this->buatPelanggan();
        $kendaraan = $this->buatKendaraan();
        $this->buatTransaksi($kendaraan, $pelanggan, 2, 4);
        $this->buatTransaksi($kendaraan, $pelanggan, 6, 8, 'dibatalkan'); // tidak boleh muncul

        $this->getJson("/api/katalog/{$kendaraan->id}")
            ->assertOk()
            ->assertJsonCount(1, 'jadwal')
            ->assertJsonPath('jadwal.0.tanggal_mulai', $this->hari(2))
            ->assertJsonPath('jadwal.0.tanggal_akhir', $this->hari(4));
    }
}
