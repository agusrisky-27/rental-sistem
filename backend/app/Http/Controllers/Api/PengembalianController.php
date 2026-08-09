<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class PengembalianController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengembalian::with(['transaksi.kendaraan', 'transaksi.pelanggan'])->latest();
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaksi_id'      => 'required|exists:transaksi,id',
            'tanggal_kembali'   => 'required|date',
            'kondisi_kendaraan' => 'nullable|string',
        ]);

        $transaksi = Transaksi::findOrFail($data['transaksi_id']);

        // Hitung denda
        $rencana = $transaksi->tanggal_akhir;
        $aktual  = $data['tanggal_kembali'];
        $telat   = max(0, now()->parse($aktual)->diffInDays($rencana, false) * -1);
        $denda   = $telat > 0 ? ($transaksi->kendaraan->harga * $telat) : 0;

        $pengembalian = Pengembalian::create([
            ...$data,
            'denda'  => $denda,
            'status' => $denda > 0 ? 'terlambat' : 'tepat waktu',
        ]);

        return response()->json($pengembalian->load('transaksi'), 201);
    }

    public function show(Pengembalian $pengembalian)
    {
        return response()->json($pengembalian->load('transaksi.kendaraan', 'transaksi.pelanggan'));
    }

    public function update(Request $request, Pengembalian $pengembalian)
    {
        $pengembalian->update($request->only(['kondisi_kendaraan', 'status']));
        return response()->json($pengembalian);
    }

    public function destroy(Pengembalian $pengembalian)
    {
        $pengembalian->delete();
        return response()->json(['message' => 'Data dihapus.']);
    }

    public function terima(Pengembalian $pengembalian)
    {
        $pengembalian->update(['status' => 'selesai']);
        $pengembalian->transaksi->update(['status' => 'selesai']);
        $pengembalian->transaksi->kendaraan->update(['status' => 'tersedia']);
        return response()->json($pengembalian);
    }
}
