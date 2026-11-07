<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Pengembalian;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['kendaraan', 'pelanggan', 'pembayaran', 'pengembalian'])->latest();

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
            'kendaraan_id'  => 'required|exists:kendaraan,id,deleted_at,NULL',
            'pelanggan_id'  => 'required|exists:pelanggan,id,deleted_at,NULL',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after:tanggal_mulai',
        ]);

        $transaksi = Transaksi::buat($data['kendaraan_id'], $data['pelanggan_id'], $data['tanggal_mulai'], $data['tanggal_akhir']);

        return response()->json($transaksi->load(['kendaraan', 'pelanggan']), 201);
    }

    public function show(Transaksi $transaksi)
    {
        return response()->json($transaksi->load(['kendaraan', 'pelanggan', 'semuaPembayaran', 'pengembalian']));
    }

    // Admin hanya boleh membatalkan/menolak booking yang masih menunggu
    public function update(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'status' => 'required|in:dibatalkan',
        ]);

        $transaksi->batalkan();

        Notification::keUser($transaksi->userPelangganId(), 'transaction', 'Booking dibatalkan',
            "Booking #{$transaksi->id} dibatalkan oleh admin.", ['transaksi_id' => $transaksi->id]);

        return response()->json($transaksi->load(['kendaraan', 'pelanggan']));
    }

    public function destroy(Transaksi $transaksi)
    {
        // Riwayat sewa & pendapatan tidak boleh hilang; hanya booking batal yang boleh dihapus
        $transaksi->pastikanStatus('dibatalkan');
        $transaksi->delete();
        return response()->json(['message' => 'Transaksi dihapus.']);
    }

    // Konfirmasi manual = pelanggan bayar tunai di tempat
    public function konfirmasi(Transaksi $transaksi)
    {
        $transaksi->pastikanStatus('menunggu');

        if ($transaksi->semuaPembayaran()->where('jenis', 'sewa')->where('status', 'menunggu verifikasi')->exists()) {
            return response()->json(['message' => 'Ada bukti pembayaran yang menunggu verifikasi. Verifikasi di menu Pembayaran.'], 422);
        }

        DB::transaction(function () use ($transaksi) {
            Pembayaran::create([
                'transaksi_id'  => $transaksi->id,
                'jenis'         => 'sewa',
                'metode'        => 'tunai',
                'jumlah'        => $transaksi->total,
                'status'        => 'berhasil',
                'tanggal_bayar' => now(),
            ]);
            $transaksi->aktifkan();
        });

        return response()->json($transaksi->fresh(['kendaraan', 'pelanggan']));
    }

    public function selesai(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'tanggal_kembali'   => 'nullable|date',
            'kondisi_kendaraan' => 'nullable|string|max:1000',
        ]);

        Pengembalian::catat($transaksi, $data['tanggal_kembali'] ?? now()->toDateString(), $data['kondisi_kendaraan'] ?? null);

        return response()->json($transaksi->fresh(['kendaraan', 'pelanggan', 'pengembalian']));
    }
}
