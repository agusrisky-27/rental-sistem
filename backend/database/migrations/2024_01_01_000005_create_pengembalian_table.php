<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengembalian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $table->date('tanggal_kembali');
            $table->text('kondisi_kendaraan')->nullable();
            $table->bigInteger('denda')->default(0);
            $table->enum('status', ['tepat waktu', 'terlambat', 'selesai'])->default('tepat waktu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengembalian');
    }
};
