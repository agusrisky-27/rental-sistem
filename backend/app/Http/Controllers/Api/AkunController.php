<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Endpoint milik pelanggan yang login (route group role:pelanggan)
class AkunController extends Controller
{
    private function pelanggan(Request $request): Pelanggan
    {
        return $request->user()->pelanggan ?? abort(403, 'Akses ditolak.');
    }

    // Cegah IDOR: pelanggan hanya boleh menyentuh transaksi miliknya
    private function milikSendiri(Request $request, Transaksi $transaksi): Transaksi
    {
        abort_unless((int) $transaksi->pelanggan_id === (int) $this->pelanggan($request)->id, 403, 'Akses ditolak.');
        return $transaksi;
    }

    public function index(Request $request)
    {
        return response()->json(
            Transaksi::where('pelanggan_id', $this->pelanggan($request)->id)
                ->with(['kendaraan', 'pembayaran', 'semuaPembayaran', 'pengembalian'])
                ->latest()
                ->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kendaraan_id'  => 'required|exists:kendaraan,id,deleted_at,NULL',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_akhir' => 'required|date|after:tanggal_mulai',
        ]);

        $pelanggan = $this->pelanggan($request);
        $transaksi = Transaksi::buat($data['kendaraan_id'], $pelanggan->id, $data['tanggal_mulai'], $data['tanggal_akhir']);

        Notification::keAdmin('transaction', 'Booking baru',
            "{$pelanggan->nama} membooking {$transaksi->kendaraan->nama} ({$transaksi->tanggal_mulai} s/d {$transaksi->tanggal_akhir}).",
            ['transaksi_id' => $transaksi->id]);

        return response()->json($transaksi->load('kendaraan'), 201);
    }

    public function show(Request $request, Transaksi $transaksi)
    {
        return response()->json(
            $this->milikSendiri($request, $transaksi)->load(['kendaraan', 'pembayaran', 'semuaPembayaran', 'pengembalian'])
        );
    }

    public function batal(Request $request, Transaksi $transaksi)
    {
        $this->milikSendiri($request, $transaksi);

        if ($transaksi->semuaPembayaran()->where('jenis', 'sewa')->whereIn('status', ['menunggu verifikasi', 'berhasil'])->exists()) {
            return response()->json(['message' => 'Booking yang sudah dibayar tidak bisa dibatalkan. Hubungi admin.'], 422);
        }

        $transaksi->batalkan();

        Notification::keAdmin('transaction', 'Booking dibatalkan pelanggan',
            "Booking #{$transaksi->id} dibatalkan oleh pelanggan.", ['transaksi_id' => $transaksi->id]);

        return response()->json($transaksi->load('kendaraan'));
    }

    public function bayar(Request $request, Transaksi $transaksi)
    {
        $this->milikSendiri($request, $transaksi);

        $data = $request->validate([
            'jenis'  => 'required|in:sewa,denda',
            'metode' => 'required|string|max:50',
            'bukti'  => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $pembayaran = Pembayaran::ajukan($transaksi, $data['jenis'], $data['metode'], $request->file('bukti'));

        return response()->json($pembayaran, 201);
    }

    public function profil(Request $request)
    {
        return response()->json($this->pelanggan($request));
    }

    public function updateProfil(Request $request)
    {
        $pelanggan = $this->pelanggan($request);

        $data = $request->validate([
            'nama'     => 'required|string|max:255',
            'telepon'  => 'required|string|max:20',
            'alamat'   => 'nullable|string|max:1000',
            'foto_ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('foto_ktp')) {
            if ($pelanggan->foto_ktp) Storage::disk('public')->delete($pelanggan->foto_ktp);
            $data['foto_ktp'] = $request->file('foto_ktp')->store('ktp', 'public');
        } else {
            unset($data['foto_ktp']);
        }

        $pelanggan->update($data);
        $request->user()->update(['name' => $data['nama'], 'telepon' => $data['telepon']]);

        return response()->json($pelanggan);
    }
}
