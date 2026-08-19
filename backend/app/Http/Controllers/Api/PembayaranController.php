<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with(['transaksi.pelanggan', 'transaksi.kendaraan'])->latest();
        if ($request->status) $query->where('status', $request->status);
        $perPage = min($request->input('per_page', 10), 1000);
        return response()->json($query->paginate($perPage));
    }

    public function stats()
    {
        $today = Carbon::today();

        return response()->json([
            'menunggu_verifikasi' => Pembayaran::where('status', 'menunggu verifikasi')->count(),
            'berhasil_hari_ini'   => Pembayaran::where('status', 'berhasil')->whereDate('tanggal_bayar', $today)->count(),
            'total_pending'       => Pembayaran::where('status', 'menunggu verifikasi')->sum('jumlah'),
        ]);
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
        $data = $request->validate([
            'status' => 'required|in:menunggu verifikasi,berhasil,ditolak',
        ]);

        $pembayaran->update($data);
        return response()->json($pembayaran->load('transaksi.pelanggan', 'transaksi.kendaraan'));
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
