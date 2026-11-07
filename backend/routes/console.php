<?php

use App\Models\Notification;
use App\Models\Transaksi;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Booking tanpa bukti bayar (pending/berhasil) lebih dari 24 jam dibatalkan otomatis
Artisan::command('transaksi:expire', function () {
    $jumlah = 0;

    Transaksi::where('status', 'menunggu')
        ->where('created_at', '<=', now()->subDay())
        ->whereDoesntHave('semuaPembayaran', fn ($q) => $q->whereIn('status', ['menunggu verifikasi', 'berhasil']))
        ->get() // bukan ->each() di query: chunk berbasis offset melewatkan baris yang statusnya diubah
        ->each(function (Transaksi $transaksi) use (&$jumlah) {
            $transaksi->batalkan();
            Notification::keUser($transaksi->userPelangganId(), 'transaction', 'Booking kadaluarsa',
                "Booking #{$transaksi->id} dibatalkan otomatis karena belum ada pembayaran dalam 24 jam.",
                ['transaksi_id' => $transaksi->id]);
            $jumlah++;
        });

    $this->info("{$jumlah} booking kadaluarsa dibatalkan.");
})->purpose('Batalkan booking menunggu yang belum dibayar lebih dari 24 jam');

Schedule::command('transaksi:expire')->hourly();
