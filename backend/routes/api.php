<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AkunController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KatalogController;
use App\Http\Controllers\Api\KendaraanController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PengembalianController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;

// Public routes
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

Route::get('/katalog',             [KatalogController::class, 'index']);
Route::get('/katalog/{kendaraan}', [KatalogController::class, 'show']);
Route::get('/settings/public',     [SettingController::class, 'publik']);

// Protected routes (semua role)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::patch('/auth/password', [AuthController::class, 'updatePassword']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read']);

    // Pelanggan
    Route::middleware('role:pelanggan')->prefix('akun')->group(function () {
        Route::get('/transaksi',                         [AkunController::class, 'index']);
        Route::post('/transaksi',                        [AkunController::class, 'store']);
        Route::get('/transaksi/{transaksi}',             [AkunController::class, 'show']);
        Route::patch('/transaksi/{transaksi}/batal',     [AkunController::class, 'batal']);
        Route::post('/transaksi/{transaksi}/pembayaran', [AkunController::class, 'bayar']);
        Route::get('/profil',                            [AkunController::class, 'profil']);
        Route::post('/profil',                           [AkunController::class, 'updateProfil']);
    });

    // Admin
    Route::middleware('role:admin')->group(function () {
        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        // Pembayaran stats (harus didaftarkan sebelum apiResource agar tidak bentrok dengan /pembayaran/{pembayaran})
        Route::get('/pembayaran-stats', [PembayaranController::class, 'stats']);

        // Laporan
        Route::get('/laporan/pendapatan',        [LaporanController::class, 'pendapatan']);
        Route::get('/laporan/pendapatan/export', [LaporanController::class, 'export']);

        // Resources
        Route::apiResource('kendaraan',    KendaraanController::class);
        Route::apiResource('pelanggan',    PelangganController::class);
        Route::apiResource('transaksi',    TransaksiController::class);
        Route::apiResource('pembayaran',   PembayaranController::class);
        Route::apiResource('pengembalian', PengembalianController::class);

        // Custom actions
        Route::patch('/transaksi/{transaksi}/konfirmasi',    [TransaksiController::class,    'konfirmasi']);
        Route::patch('/transaksi/{transaksi}/selesai',       [TransaksiController::class,    'selesai']);
        Route::patch('/pembayaran/{pembayaran}/verifikasi',  [PembayaranController::class,   'verifikasi']);
        Route::patch('/pengembalian/{pengembalian}/lunas',   [PengembalianController::class, 'lunas']);

        Route::get('/settings', [SettingController::class, 'index']);
        Route::patch('/settings', [SettingController::class, 'update']);
    });
});
