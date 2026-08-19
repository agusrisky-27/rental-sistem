<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KendaraanController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PengembalianController;
use App\Http\Controllers\Api\DashboardController;

// Public routes
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Pembayaran stats (harus didaftarkan sebelum apiResource agar tidak bentrok dengan /pembayaran/{pembayaran})
    Route::get('/pembayaran-stats', [PembayaranController::class, 'stats']);

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
    Route::patch('/pengembalian/{pengembalian}/terima',  [PengembalianController::class, 'terima']);
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index']);
    Route::patch('/settings', [\App\Http\Controllers\SettingController::class, 'update']);

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll']);
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'read']);

    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::patch('/auth/password', [AuthController::class, 'updatePassword']);
});
