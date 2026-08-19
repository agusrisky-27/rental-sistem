<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Kendaraan;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['kendaraan', 'pelanggan'])->latest();

        if ($request->status)        $query->where('status', $request->status);
        if ($request->tanggal_mulai) $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        if ($request->tanggal_akhir) $query->whereDate('tanggal_akhir', '<=', $request->tanggal_akhir);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', fn ($p) => $p->where('nama', 'like', "%{$search}%"))
                  ->orWhereHas('kendaraan', fn ($k) => $k->where('nama', 'like', "%{$search}%")
                                                          ->orWhere('plat', 'like', "%{$search}%"));
            });
        }

        $perPage = min($request->input('per_page', 10), 1000);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kendaraan_id'  => 'required|exists:kendaraan,id',
            'pelanggan_id'  => 'required|exists:pelanggan,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after:tanggal_mulai',
        ]);

        $kendaraan = Kendaraan::findOrFail($data['kendaraan_id']);
        $durasi    = Carbon::parse($data['tanggal_mulai'])->diffInDays(Carbon::parse($data['tanggal_akhir']));
        $total     = $kendaraan->harga * max(1, $durasi);

        $transaksi = Transaksi::create([
            ...$data,
            'total'  => $total,
            'status' => 'menunggu',
        ]);

        $kendaraan->update(['status' => 'disewa']);

        return response()->json($transaksi->load(['kendaraan', 'pelanggan']), 201);
    }

    public function show(Transaksi $transaksi)
    {
        return response()->json($transaksi->load(['kendaraan', 'pelanggan']));
    }

    public function update(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'status' => 'required|in:menunggu,aktif,selesai,dibatalkan',
        ]);

        // Jika transaksi dibatalkan/ditolak, kembalikan status kendaraan menjadi tersedia
        if ($data['status'] === 'dibatalkan' && $transaksi->status !== 'dibatalkan') {
            $transaksi->kendaraan->update(['status' => 'tersedia']);
        }

        $transaksi->update($data);
        return response()->json($transaksi->load(['kendaraan', 'pelanggan']));
    }

    public function destroy(Transaksi $transaksi)
    {
        $transaksi->delete();
        return response()->json(['message' => 'Transaksi dihapus.']);
    }

    public function konfirmasi(Transaksi $transaksi)
    {
        $transaksi->update(['status' => 'aktif']);
        return response()->json($transaksi);
    }

    public function selesai(Transaksi $transaksi)
    {
        // Catat pengembalian jika belum ada, sekaligus hitung denda keterlambatan
        if (! $transaksi->pengembalian) {
            $rencana = Carbon::parse($transaksi->tanggal_akhir);
            $aktual  = Carbon::now();
            $telat   = $aktual->gt($rencana) ? $aktual->diffInDays($rencana) : 0;
            $denda   = $telat > 0 ? ($transaksi->kendaraan->harga * $telat) : 0;

            Pengembalian::create([
                'transaksi_id'      => $transaksi->id,
                'tanggal_kembali'   => $aktual->toDateString(),
                'kondisi_kendaraan' => null,
                'denda'             => $denda,
                'status'            => 'selesai',
            ]);
        }

        $transaksi->update(['status' => 'selesai']);
        $transaksi->kendaraan->update(['status' => 'tersedia']);
        return response()->json($transaksi->load('pengembalian'));
    }
}
