<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Kendaraan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['kendaraan', 'pelanggan'])->latest();

        if ($request->status)        $query->where('status', $request->status);
        if ($request->tanggal_mulai) $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        if ($request->tanggal_akhir) $query->whereDate('tanggal_mulai', '<=', $request->tanggal_akhir);

        return response()->json($query->paginate(10));
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
        $transaksi->update($request->only(['status']));
        return response()->json($transaksi);
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
        $transaksi->update(['status' => 'selesai']);
        $transaksi->kendaraan->update(['status' => 'tersedia']);
        return response()->json($transaksi);
    }
}
