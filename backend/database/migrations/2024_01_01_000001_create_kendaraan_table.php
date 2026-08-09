<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tipe');
            $table->string('plat')->unique();
            $table->integer('kapasitas');
            $table->bigInteger('harga');
            $table->integer('tahun');
            $table->string('warna')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['tersedia', 'disewa', 'maintenance'])->default('tersedia');
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
