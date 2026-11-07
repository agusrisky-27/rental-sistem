<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\BuatData;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase, BuatData;

    private int $tahun;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tahun = now()->year;
        Sanctum::actingAs($this->buatAdmin());
    }

    private function bayar(int $transaksiId, string $jenis, string $status, int $jumlah, Carbon $tanggal): void
    {
        Pembayaran::create([
            'transaksi_id' => $transaksiId, 'jenis' => $jenis, 'metode' => 'tunai',
            'jumlah' => $jumlah, 'status' => $status, 'tanggal_bayar' => $tanggal,
        ]);
    }

    private function seedData(): void
    {
        $pelanggan = $this->buatPelanggan();
        $kendaraan = $this->buatKendaraan();

        // Transaksi dibuat Januari tahun ini (dihitung)
        $t = $this->buatTransaksi($kendaraan, $pelanggan, 1, 3, 'selesai');
        $t->forceFill(['created_at' => Carbon::create($this->tahun, 1, 5, 10)])->save();
        // Dibatalkan (tidak dihitung) dan tahun lalu (di luar laporan)
        $this->buatTransaksi($kendaraan, $pelanggan, 10, 12, 'dibatalkan')
            ->forceFill(['created_at' => Carbon::create($this->tahun, 1, 6, 10)])->save();
        $this->buatTransaksi($kendaraan, $pelanggan, 20, 22, 'selesai')
            ->forceFill(['created_at' => Carbon::create($this->tahun - 1, 1, 6, 10)])->save();

        $this->bayar($t->id, 'sewa', 'berhasil', 500000, Carbon::create($this->tahun, 1, 15, 9));
        $this->bayar($t->id, 'denda', 'berhasil', 100000, Carbon::create($this->tahun, 3, 10, 9));
        // Tidak dihitung: belum diverifikasi, ditolak, dan tahun lain
        $this->bayar($t->id, 'denda', 'menunggu verifikasi', 999999, Carbon::create($this->tahun, 3, 11, 9));
        $this->bayar($t->id, 'sewa', 'ditolak', 888888, Carbon::create($this->tahun, 2, 11, 9));
        $this->bayar($t->id, 'sewa', 'berhasil', 777777, Carbon::create($this->tahun - 1, 12, 31, 9));
        // Denda belum bayar: tidak ada pembayaran, jadi tidak dihitung
        Pengembalian::create([
            'transaksi_id' => $t->id, 'tanggal_kembali' => now()->toDateString(), 'denda' => 250000,
            'status' => 'terlambat', 'denda_status' => 'belum bayar',
        ]);
    }

    public function test_pendapatan_12_bulan_dan_denda_belum_bayar_tidak_dihitung(): void
    {
        $this->seedData();

        $res = $this->getJson("/api/laporan/pendapatan?tahun={$this->tahun}")->assertOk();

        $this->assertSame($this->tahun, $res->json('tahun'));
        $this->assertCount(12, $res->json('data'));
        $this->assertSame(range(1, 12), array_column($res->json('data'), 'bulan'));

        $jan = $res->json('data.0');
        $this->assertEquals(500000, $jan['sewa']);
        $this->assertEquals(0, $jan['denda']);
        $this->assertEquals(500000, $jan['total']);
        $this->assertSame(1, $jan['jumlah_transaksi']);

        $mar = $res->json('data.2');
        $this->assertEquals(0, $mar['sewa']);
        $this->assertEquals(100000, $mar['denda']);

        $feb = $res->json('data.1');
        $this->assertEquals(0, $feb['total']);

        $this->assertEquals(600000, $res->json('total'));
    }

    public function test_tahun_di_luar_rentang_ditolak_422(): void
    {
        $this->getJson('/api/laporan/pendapatan?tahun=1999')->assertStatus(422);
    }

    public function test_export_csv(): void
    {
        $this->seedData();

        $res = $this->get("/api/laporan/pendapatan/export?tahun={$this->tahun}")->assertOk();

        $this->assertStringContainsString('text/csv', $res->headers->get('Content-Type'));
        $this->assertStringContainsString("laporan-pendapatan-{$this->tahun}.csv", $res->headers->get('Content-Disposition'));

        $csv = $res->streamedContent();
        $this->assertStringStartsWith('Bulan,"Pendapatan Sewa","Pendapatan Denda",Total,"Jumlah Transaksi"', $csv);
        $this->assertStringContainsString('Januari,500000,0,500000,1', $csv);
        $this->assertStringContainsString('TOTAL,500000,100000,600000,1', $csv);
    }
}
