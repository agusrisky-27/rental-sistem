<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with(['transaksi.pelanggan', 'transaksi.kendaraan'])->latest();
        if ($request->status) $query->where('status', $request->status);
        if ($request->jenis)  $query->where('jenis', $request->jenis);
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

    // Admin mencatat pembayaran atas nama pelanggan; jumlah tetap dihitung server
    public function store(Request $request)
    {
        $data = $request->validate([
            'transaksi_id' => 'required|exists:transaksi,id',
            'jenis'        => 'nullable|in:sewa,denda',
            'metode'       => 'required|string|max:50',
            'bukti'        => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $pembayaran = Pembayaran::ajukan(
            Transaksi::findOrFail($data['transaksi_id']),
            $data['jenis'] ?? 'sewa',
            $data['metode'],
            $request->file('bukti'),
        );

        return response()->json($pembayaran, 201);
    }

    public function show(Pembayaran $pembayaran)
    {
        return response()->json($pembayaran->load('transaksi.pelanggan', 'transaksi.kendaraan'));
    }

    // Hanya untuk menolak; verifikasi lewat endpoint verifikasi
    public function update(Request $request, Pembayaran $pembayaran)
    {
        $data = $request->validate([
            'status'  => 'required|in:ditolak',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pembayaran->tolak($data['catatan'] ?? null);
        return response()->json($pembayaran->load('transaksi.pelanggan', 'transaksi.kendaraan'));
    }

    public function destroy(Pembayaran $pembayaran)
    {
        if ($pembayaran->status === 'berhasil') {
            return response()->json(['message' => 'Pembayaran yang sudah berhasil tidak bisa dihapus.'], 422);
        }
        $pembayaran->delete();
        return response()->json(['message' => 'Pembayaran dihapus.']);
    }

    public function verifikasi(Pembayaran $pembayaran)
    {
        $pembayaran->verifikasi();
        return response()->json($pembayaran->fresh('transaksi'));
    }
}
