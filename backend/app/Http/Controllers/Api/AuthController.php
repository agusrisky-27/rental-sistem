<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken('siwaken-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user->load('pelanggan'),
        ]);
    }

    // Register publik hanya membuat akun pelanggan; akun admin dibuat lewat seeder
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'telepon'  => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
        ]);

        $pelanggan = Pelanggan::withTrashed()->where('email', $data['email'])->first();
        if ($pelanggan?->user_id) {
            throw ValidationException::withMessages(['email' => ['Email sudah terdaftar.']]);
        }

        $user = DB::transaction(function () use ($data, $pelanggan) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'telepon'  => $data['telepon'],
                'password' => Hash::make($data['password']),
                'role'     => 'pelanggan',
            ]);

            if ($pelanggan) {
                // Pelanggan walk-in dengan email sama ditautkan ke akun baru
                $pelanggan->restore();
                $pelanggan->update(['user_id' => $user->id]);
            } else {
                Pelanggan::create([
                    'user_id' => $user->id,
                    'nama'    => $data['name'],
                    'email'   => $data['email'],
                    'telepon' => $data['telepon'],
                    'level'   => 'Basic',
                ]);
            }

            return $user;
        });

        Notification::keAdmin('user', 'Pelanggan baru', "{$user->name} mendaftar akun.", ['user_id' => $user->id]);

        $token = $user->createToken('siwaken-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user->load('pelanggan')], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('pelanggan'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telepon' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->telepon = $request->telepon;

        if ($request->hasFile('foto_profil')) {
            $path = $request->file('foto_profil')->store('profiles', 'public');
            $user->foto_profil = url(Storage::url($path));
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama tidak sesuai.'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah']);
    }
}
