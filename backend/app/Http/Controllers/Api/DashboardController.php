<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalPendapatan    = Pembayaran::where('status', 'berhasil')->sum('jumlah');
        $kendaraanDisewa    = Kendaraan::where('status', 'disewa')->count();
        $totalKendaraan     = Kendaraan::count();
        $penggunaBaru       = Pelanggan::whereMonth('created_at', now()->month)->count();
        $transaksiTerbaru   = Transaksi::with(['kendaraan', 'pelanggan'])->latest()->take(5)->get();

        return response()->json([
            'total_pendapatan'  => $totalPendapatan,
            'kendaraan_disewa'  => $kendaraanDisewa,
            'total_kendaraan'   => $totalKendaraan,
            'pengguna_baru'     => $penggunaBaru,
            'transaksi_terbaru' => $transaksiTerbaru,
        ]);
    }
}
