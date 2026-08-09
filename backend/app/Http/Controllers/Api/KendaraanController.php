<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KendaraanController extends Controller
{
    public function index(Request $request)
    {
        $query = Kendaraan::query();

        if ($request->search) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        if ($request->tipe) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'tipe'      => 'required|string',
            'plat'      => 'required|string|unique:kendaraan',
            'kapasitas' => 'required|integer',
            'harga'     => 'required|integer',
            'tahun'     => 'required|integer',
            'warna'     => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'status'    => 'required|in:tersedia,maintenance',
            'foto'      => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('kendaraan', 'public');
        }

        $kendaraan = Kendaraan::create($data);
        return response()->json($kendaraan, 201);
    }

    public function show(Kendaraan $kendaraan)
    {
        return response()->json($kendaraan);
    }

    public function update(Request $request, Kendaraan $kendaraan)
    {
        $data = $request->validate([
            'nama'      => 'sometimes|string|max:255',
            'tipe'      => 'sometimes|string',
            'plat'      => 'sometimes|string|unique:kendaraan,plat,' . $kendaraan->id,
            'kapasitas' => 'sometimes|integer',
            'harga'     => 'sometimes|integer',
            'tahun'     => 'sometimes|integer',
            'warna'     => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'status'    => 'sometimes|in:tersedia,disewa,maintenance',
            'foto'      => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('foto')) {
            if ($kendaraan->foto) Storage::disk('public')->delete($kendaraan->foto);
            $data['foto'] = $request->file('foto')->store('kendaraan', 'public');
        }

        $kendaraan->update($data);
        return response()->json($kendaraan);
    }

    public function destroy(Kendaraan $kendaraan)
    {
        if ($kendaraan->foto) Storage::disk('public')->delete($kendaraan->foto);
        $kendaraan->delete();
        return response()->json(['message' => 'Kendaraan dihapus.']);
    }
}
