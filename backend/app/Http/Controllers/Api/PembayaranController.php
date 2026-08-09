<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with(['transaksi.pelanggan', 'transaksi.kendaraan'])->latest();
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaksi_id' => 'required|exists:transaksi,id',
            'metode'       => 'required|string',
            'jumlah'       => 'required|integer',
            'bukti'        => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('bukti')) {
            $data['bukti'] = $request->file('bukti')->store('bukti_bayar', 'public');
        }

        $data['status'] = 'menunggu verifikasi';

        return response()->json(Pembayaran::create($data), 201);
    }

    public function show(Pembayaran $pembayaran)
    {
        return response()->json($pembayaran->load('transaksi.pelanggan', 'transaksi.kendaraan'));
    }

    public function update(Request $request, Pembayaran $pembayaran)
    {
        $pembayaran->update($request->only(['status']));
        return response()->json($pembayaran);
    }

    public function destroy(Pembayaran $pembayaran)
    {
        $pembayaran->delete();
        return response()->json(['message' => 'Pembayaran dihapus.']);
    }

    public function verifikasi(Pembayaran $pembayaran)
    {
        $pembayaran->update(['status' => 'berhasil', 'tanggal_bayar' => now()]);
        $pembayaran->transaksi->update(['status' => 'aktif']);
        return response()->json($pembayaran);
    }
}
