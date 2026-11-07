<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // updateProfile sudah memakai kolom ini, tapi migration-nya belum pernah ada
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'telepon'))     $table->string('telepon', 20)->nullable();
            if (! Schema::hasColumn('users', 'foto_profil')) $table->string('foto_profil')->nullable();
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        Schema::table('kendaraan', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('jenis')->default('sewa');
            $table->text('catatan')->nullable();
        });

        Schema::table('pengembalian', function (Blueprint $table) {
            $table->string('denda_status')->default('tidak ada');
        });

        // Data lama: status 'selesai' diganti jadi tepat waktu/terlambat sesuai denda
        DB::table('pengembalian')->where('status', 'selesai')->where('denda', '>', 0)
            ->update(['status' => 'terlambat']);
        DB::table('pengembalian')->where('status', 'selesai')
            ->update(['status' => 'tepat waktu']);
        DB::table('pengembalian')->where('denda', '>', 0)
            ->update(['denda_status' => 'belum bayar']);
    }

    public function down(): void
    {
        Schema::table('pengembalian', fn (Blueprint $table) => $table->dropColumn('denda_status'));
        Schema::table('pembayaran', fn (Blueprint $table) => $table->dropColumn(['jenis', 'catatan']));
        Schema::table('kendaraan', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropSoftDeletes();
        });
    }
};
