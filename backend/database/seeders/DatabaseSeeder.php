<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kendaraan;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'     => 'Admin Utama',
            'email'    => 'admin@siwaken.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Sample kendaraan
        $kendaraan = [
            ['nama'=>'Toyota Fortuner',     'tipe'=>'SUV',   'plat'=>'B 1234 XYZ', 'kapasitas'=>7, 'harga'=>1500000, 'tahun'=>2023, 'warna'=>'Putih',  'status'=>'tersedia'],
            ['nama'=>'Honda Camry',          'tipe'=>'Sedan', 'plat'=>'D 5678 ABC', 'kapasitas'=>5, 'harga'=>2000000, 'tahun'=>2022, 'warna'=>'Hitam',  'status'=>'disewa'],
            ['nama'=>'Toyota Innova Zenix',  'tipe'=>'MPV',   'plat'=>'L 9012 DEF', 'kapasitas'=>7, 'harga'=>1200000, 'tahun'=>2023, 'warna'=>'Silver', 'status'=>'maintenance'],
            ['nama'=>'Honda Brio',           'tipe'=>'Hatchback', 'plat'=>'B 4321 ZYX', 'kapasitas'=>5, 'harga'=>600000, 'tahun'=>2022, 'warna'=>'Merah','status'=>'tersedia'],
            ['nama'=>'Mitsubishi Xpander',   'tipe'=>'MPV',   'plat'=>'F 1111 AAA', 'kapasitas'=>7, 'harga'=>900000, 'tahun'=>2021, 'warna'=>'Putih',  'status'=>'tersedia'],
        ];
        foreach ($kendaraan as $k) Kendaraan::create($k);

        // Sample pelanggan
        $pelanggan = [
            ['nama'=>'Ahmad Santoso',  'email'=>'ahmad@email.com', 'telepon'=>'08123456789', 'level'=>'Gold'],
            ['nama'=>'Budi Wijaya',    'email'=>'budi@email.com',  'telepon'=>'08567890123', 'level'=>'Silver'],
            ['nama'=>'Citra Dewi',     'email'=>'citra@email.com', 'telepon'=>'08119876543', 'level'=>'Silver'],
            ['nama'=>'Deni Kusuma',    'email'=>'deni@email.com',  'telepon'=>'08213456789', 'level'=>'Basic'],
        ];
        foreach ($pelanggan as $p) Pelanggan::create($p);
    }
}
