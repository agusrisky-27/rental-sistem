<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::withCount('transaksi');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telepon', 'like', "%{$search}%");
            });
        }
        if ($request->level)  $query->where('level', $request->level);
        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:255',
            'email'   => 'required|email|unique:pelanggan',
            'telepon' => 'required|string',
            'alamat'  => 'nullable|string',
            'level'   => 'required|in:Basic,Silver,Gold',
            'foto_ktp'=> 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('foto_ktp')) {
            $data['foto_ktp'] = $request->file('foto_ktp')->store('ktp', 'public');
        }

        return response()->json(Pelanggan::create($data), 201);
    }

    public function show(Pelanggan $pelanggan)
    {
        return response()->json($pelanggan->load('transaksi'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate([
            'nama'     => 'sometimes|string',
            'email'    => 'sometimes|email|unique:pelanggan,email,' . $pelanggan->id,
            'telepon'  => 'sometimes|string',
            'alamat'   => 'nullable|string',
            'level'    => 'sometimes|in:Basic,Silver,Gold',
            'foto_ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('foto_ktp')) {
            if ($pelanggan->foto_ktp) Storage::disk('public')->delete($pelanggan->foto_ktp);
            $data['foto_ktp'] = $request->file('foto_ktp')->store('ktp', 'public');
        }

        $pelanggan->update($data);
        return response()->json($pelanggan);
    }

    public function destroy(Pelanggan $pelanggan)
    {
        if ($pelanggan->foto_ktp) Storage::disk('public')->delete($pelanggan->foto_ktp);
        $pelanggan->delete();
        return response()->json(['message' => 'Pelanggan dihapus.']);
    }
}
