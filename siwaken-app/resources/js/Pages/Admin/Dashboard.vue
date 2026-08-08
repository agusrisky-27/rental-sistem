<template>
  <AdminLayout pageTitle="Dashboard">
    <div class="flex justify-between items-end mb-10">
      <div>
        <h1 class="text-headline-lg font-headline-lg text-primary mb-2">Selamat Datang di Dashboard</h1>
        <p class="text-body-md font-body-md text-on-surface-variant">Berikut adalah ringkasan kinerja, pelanggan, dan aktivitas transaksi armada Anda.</p>
      </div>
      <button class="flex items-center gap-2 bg-secondary text-on-primary font-label-md text-label-md py-2 px-4 rounded-lg hover:bg-secondary-container transition-colors font-bold shadow-md">
        <span class="material-symbols-outlined text-sm">download</span>
        Cetak Laporan (Excel)
      </button>
    </div>

    <!-- Stats Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
      <!-- Stat Card 1 -->
      <div class="bg-white/70 backdrop-blur-md rounded-xl p-6 shadow-sm border border-white/20 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary-fixed/30 rounded-full blur-xl group-hover:bg-primary-fixed/50 transition-all"></div>
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-secondary-fixed flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined">payments</span>
          </div>
          <span class="text-label-md font-label-md text-on-surface-variant">Total Pendapatan</span>
        </div>
        <div class="text-headline-md font-headline-md text-primary">Rp 48.5M</div>
        <div class="mt-2 text-label-sm font-label-sm text-secondary flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">trending_up</span> +15% dari bulan lalu
        </div>
      </div>

      <!-- Stat Card 2 -->
      <div class="bg-white/70 backdrop-blur-md rounded-xl p-6 shadow-sm border border-white/20 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-error-container/30 rounded-full blur-xl group-hover:bg-error-container/50 transition-all"></div>
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface">
            <span class="material-symbols-outlined">directions_car</span>
          </div>
          <span class="text-label-md font-label-md text-on-surface-variant">Kendaraan Disewa</span>
        </div>
        <div class="text-headline-md font-headline-md text-primary">86 / 124</div>
        <div class="mt-2 text-label-sm font-label-sm text-on-surface-variant flex items-center gap-1">
          Sedang digunakan hari ini
        </div>
      </div>

      <!-- Stat Card 3 -->
      <div class="bg-white/70 backdrop-blur-md rounded-xl p-6 shadow-sm border border-white/20 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-secondary-fixed-dim/30 rounded-full blur-xl group-hover:bg-secondary-fixed-dim/50 transition-all"></div>
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-primary-fixed-dim flex items-center justify-center text-primary-container">
            <span class="material-symbols-outlined">group_add</span>
          </div>
          <span class="text-label-md font-label-md text-on-surface-variant">Pengguna Baru</span>
        </div>
        <div class="text-headline-md font-headline-md text-primary">342</div>
        <div class="mt-2 text-label-sm font-label-sm text-secondary flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">trending_up</span> +8% minggu ini
        </div>
      </div>

      <!-- Stat Card 4 (Action) -->
      <div class="bg-white/70 backdrop-blur-md rounded-xl p-6 shadow-sm border border-white/20 relative overflow-hidden group flex flex-col justify-between">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-secondary-fixed/30 rounded-full blur-xl group-hover:bg-secondary-fixed/50 transition-all"></div>
        <div>
          <h3 class="text-headline-md font-headline-md text-primary mb-2">Transaksi Cepat</h3>
          <p class="text-body-md font-body-md text-on-surface-variant">Buat pesanan baru untuk pelanggan secara langsung.</p>
        </div>
        <button class="mt-4 w-full bg-secondary text-on-primary font-label-md text-label-md py-3 rounded-lg hover:bg-secondary-container transition-colors font-bold shadow-md relative z-10">
          + Buat Pesanan
        </button>
      </div>
    </div>

    <!-- Recent Activity Table Section -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-outline-variant flex justify-between items-center">
        <h2 class="text-headline-md font-headline-md text-primary">Aktivitas Terbaru</h2>
        <button class="text-secondary text-label-md font-label-md hover:underline">Lihat Semua</button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low text-on-surface-variant text-label-sm font-label-sm uppercase tracking-wider">
              <th class="p-4 font-semibold">Kendaraan</th>
              <th class="p-4 font-semibold">Pelanggan</th>
              <th class="p-4 font-semibold">Tanggal Sewa</th>
              <th class="p-4 font-semibold">Status</th>
              <th class="p-4 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-body-md font-body-md divide-y divide-outline-variant/30">
            <!-- Row 1 -->
            <tr class="hover:bg-surface-container-low/50 transition-colors">
              <td class="p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-md bg-surface-variant flex items-center justify-center">
                  <span class="material-symbols-outlined text-outline">directions_car</span>
                </div>
                <span class="font-semibold text-primary">Toyota Avanza 2023</span>
              </td>
              <td class="p-4 text-on-surface-variant">Budi Santoso</td>
              <td class="p-4 text-on-surface-variant">12 Okt - 15 Okt 2024</td>
              <td class="p-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm font-label-sm font-bold bg-secondary-fixed text-on-secondary-fixed">
                  Aktif
                </span>
              </td>
              <td class="p-4 text-right">
                <button class="text-outline hover:text-primary transition-colors p-1">
                  <span class="material-symbols-outlined">more_vert</span>
                </button>
              </td>
            </tr>
            <!-- Row 2 -->
            <tr class="hover:bg-surface-container-low/50 transition-colors">
              <td class="p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-md bg-surface-variant flex items-center justify-center">
                  <span class="material-symbols-outlined text-outline">directions_car</span>
                </div>
                <span class="font-semibold text-primary">Honda CR-V</span>
              </td>
              <td class="p-4 text-on-surface-variant">Siti Aminah</td>
              <td class="p-4 text-on-surface-variant">10 Okt - 11 Okt 2024</td>
              <td class="p-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm font-label-sm font-bold bg-surface-container-highest text-on-surface-variant">
                  Selesai
                </span>
              </td>
              <td class="p-4 text-right">
                <button class="text-outline hover:text-primary transition-colors p-1">
                  <span class="material-symbols-outlined">more_vert</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>
