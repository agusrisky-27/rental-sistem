<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\Pengembalian;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $now = Carbon::now();
        $startThisMonth = $now->copy()->startOfMonth();
        $startLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endLastMonth   = $now->copy()->subMonthNoOverflow()->endOfMonth();

        // Pendapatan: pembayaran berhasil + denda keterlambatan
        $pendapatanBulanIni = Pembayaran::where('status', 'berhasil')
            ->whereBetween('tanggal_bayar', [$startThisMonth, $now])
            ->sum('jumlah')
            + Pengembalian::whereBetween('created_at', [$startThisMonth, $now])->sum('denda');

        $pendapatanBulanLalu = Pembayaran::where('status', 'berhasil')
            ->whereBetween('tanggal_bayar', [$startLastMonth, $endLastMonth])
            ->sum('jumlah')
            + Pengembalian::whereBetween('created_at', [$startLastMonth, $endLastMonth])->sum('denda');

        $totalPendapatan = Pembayaran::where('status', 'berhasil')->sum('jumlah') + Pengembalian::sum('denda');

        $trendPendapatan = $pendapatanBulanLalu > 0
            ? round((($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100, 1)
            : ($pendapatanBulanIni > 0 ? 100 : 0);

        $kendaraanDisewa = Kendaraan::where('status', 'disewa')->count();
        $totalKendaraan  = Kendaraan::count();

        // Pelanggan aktif = pelanggan yang sedang punya transaksi berstatus aktif
        $pelangganAktif = Pelanggan::whereHas('transaksi', fn ($q) => $q->where('status', 'aktif'))->count();

        // Pengguna baru bulan berjalan (dengan whereYear agar tidak salah tangkap tahun lalu)
        $penggunaBaru = Pelanggan::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $startThisWeek = $now->copy()->startOfWeek();
        $startLastWeek = $now->copy()->subWeek()->startOfWeek();
        $endLastWeek   = $now->copy()->subWeek()->endOfWeek();

        $penggunaMingguIni  = Pelanggan::where('created_at', '>=', $startThisWeek)->count();
        $penggunaMingguLalu = Pelanggan::whereBetween('created_at', [$startLastWeek, $endLastWeek])->count();

        $trendPelanggan = $penggunaMingguLalu > 0
            ? round((($penggunaMingguIni - $penggunaMingguLalu) / $penggunaMingguLalu) * 100, 1)
            : ($penggunaMingguIni > 0 ? 100 : 0);

        $transaksiTerbaru = Transaksi::with(['kendaraan', 'pelanggan'])->latest()->take(5)->get();

        return response()->json([
            'total_pendapatan'  => $totalPendapatan,
            'trend_pendapatan'  => $trendPendapatan,
            'kendaraan_disewa'  => $kendaraanDisewa,
            'total_kendaraan'   => $totalKendaraan,
            'pelanggan_aktif'   => $pelangganAktif,
            'pengguna_baru'     => $penggunaBaru,
            'trend_pelanggan'   => $trendPelanggan,
            'transaksi_terbaru' => $transaksiTerbaru,
        ]);
    }
}
