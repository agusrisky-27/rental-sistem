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
        if ($request->status)       $query->where('status', $request->status);
        if ($request->denda_status) $query->where('denda_status', $request->denda_status);
        $perPage = min($request->input('per_page', 10), 1000);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaksi_id'      => 'required|exists:transaksi,id',
            'tanggal_kembali'   => 'required|date',
            'kondisi_kendaraan' => 'nullable|string|max:1000',
        ]);

        $pengembalian = Pengembalian::catat(
            Transaksi::findOrFail($data['transaksi_id']),
            $data['tanggal_kembali'],
            $data['kondisi_kendaraan'] ?? null,
        );

        return response()->json($pengembalian->load('transaksi'), 201);
    }

    public function show(Pengembalian $pengembalian)
    {
        return response()->json($pengembalian->load('transaksi.kendaraan', 'transaksi.pelanggan'));
    }

    public function update(Request $request, Pengembalian $pengembalian)
    {
        $data = $request->validate([
            'kondisi_kendaraan' => 'nullable|string|max:1000',
        ]);

        $pengembalian->update($data);
        return response()->json($pengembalian);
    }

    public function destroy(Pengembalian $pengembalian)
    {
        $pengembalian->delete();
        return response()->json(['message' => 'Data dihapus.']);
    }

    public function lunas(Pengembalian $pengembalian)
    {
        $pengembalian->lunasi();
        return response()->json($pengembalian->fresh('transaksi.kendaraan', 'transaksi.pelanggan'));
    }
}
