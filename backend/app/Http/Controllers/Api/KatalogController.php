<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\Transaksi;
use Illuminate\Http\Request;

// Endpoint publik untuk halaman katalog pelanggan
class KatalogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after:tanggal_mulai',
        ]);

        $query = Kendaraan::where('status', '!=', 'maintenance');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('tipe', 'like', "%{$search}%");
            });
        }
        if ($request->tipe) $query->where('tipe', $request->tipe);

        if ($request->tanggal_mulai && $request->tanggal_akhir) {
            $query->whereDoesntHave('transaksi', fn ($t) => $t->bentrok($request->tanggal_mulai, $request->tanggal_akhir));
        }

        return response()->json($query->orderBy('nama')->paginate(12));
    }

    public function show(Kendaraan $kendaraan)
    {
        $jadwal = $kendaraan->transaksi()
            ->whereIn('status', Transaksi::STATUS_JADWAL)
            ->where('tanggal_akhir', '>=', now()->toDateString())
            ->orderBy('tanggal_mulai')
            ->get(['tanggal_mulai', 'tanggal_akhir']);

        return response()->json([...$kendaraan->toArray(), 'jadwal' => $jadwal]);
    }
}
