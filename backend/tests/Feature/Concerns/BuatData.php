<?php

namespace Tests\Feature\Concerns;

use App\Models\Kendaraan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;

trait BuatData
{
    protected function buatAdmin(): User
    {
        // firstOrCreate: aman dipanggil berkali-kali dalam satu test (email unik)
        return User::firstOrCreate(['email' => 'admin@test.com'], ['name' => 'Admin', 'password' => 'password', 'role' => 'admin']);
    }

    // Pelanggan beserta akun user-nya (akses user lewat $pelanggan->user)
    protected function buatPelanggan(string $email = 'pelanggan@test.com'): Pelanggan
    {
        $user = User::create(['name' => 'Budi', 'email' => $email, 'password' => 'password', 'role' => 'pelanggan']);

        return Pelanggan::create([
            'user_id' => $user->id, 'nama' => 'Budi', 'email' => $email,
            'telepon' => '081234567890', 'level' => 'Basic',
        ]);
    }

    protected function buatKendaraan(array $attr = []): Kendaraan
    {
        static $n = 0;
        $n++;

        return Kendaraan::create($attr + [
            'nama' => "Avanza {$n}", 'tipe' => 'MPV', 'plat' => "B {$n} TST", 'kapasitas' => 7,
            'harga' => 500000, 'tahun' => 2022, 'status' => 'tersedia',
        ]);
    }

    // $mulai/$akhir = selisih hari dari hari ini (boleh negatif)
    protected function buatTransaksi(Kendaraan $kendaraan, Pelanggan $pelanggan, int $mulai, int $akhir, string $status = 'menunggu'): Transaksi
    {
        return Transaksi::create([
            'kendaraan_id'  => $kendaraan->id,
            'pelanggan_id'  => $pelanggan->id,
            'tanggal_mulai' => now()->addDays($mulai)->toDateString(),
            'tanggal_akhir' => now()->addDays($akhir)->toDateString(),
            'total'         => $kendaraan->harga * max(1, $akhir - $mulai),
            'status'        => $status,
        ]);
    }
}
