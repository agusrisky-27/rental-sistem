<template>
  <AdminLayout pageTitle="Manajemen Transaksi">
    <div class="space-y-6">
      <!-- Filters and Actions -->
      <div class="bg-white/70 backdrop-blur-md rounded-xl shadow-sm p-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4 border border-white/20">
        <div class="flex flex-wrap gap-4 w-full md:w-auto">
          <!-- Date Range -->
          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-label-sm font-label-sm text-on-surface-variant">Tanggal Mulai</label>
            <input type="date" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md font-body-md focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow w-full sm:w-auto">
          </div>
          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-label-sm font-label-sm text-on-surface-variant">Tanggal Akhir</label>
            <input type="date" class="px-3 py-2 border border-outline-variant rounded-lg text-body-md font-body-md focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow w-full sm:w-auto">
          </div>
          
          <!-- Status Filter -->
          <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-label-sm font-label-sm text-on-surface-variant">Status</label>
            <select class="px-3 py-2 border border-outline-variant rounded-lg text-body-md font-body-md focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition-shadow w-full sm:w-auto bg-white">
              <option value="">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="selesai">Selesai</option>
              <option value="dibatalkan">Dibatalkan</option>
            </select>
          </div>
          
          <!-- Filter Button -->
          <div class="flex items-end w-full sm:w-auto">
            <button class="bg-surface-container-low text-on-surface hover:bg-surface-container transition-colors px-4 py-2 rounded-lg font-label-md text-label-md flex items-center gap-2 border border-outline-variant h-[42px] w-full justify-center">
              <span class="material-symbols-outlined text-sm" data-icon="filter_list">filter_list</span>
              Filter
            </button>
          </div>
        </div>
        
        <!-- Export Button -->
        <button class="bg-secondary text-on-secondary hover:bg-secondary-container transition-colors px-6 py-2 rounded-lg font-label-md text-label-md font-bold flex items-center gap-2 h-[42px] w-full md:w-auto justify-center whitespace-nowrap shadow-sm hover:shadow-md">
          <span class="material-symbols-outlined text-sm" data-icon="download">download</span>
          Export Excel
        </button>
      </div>

      <!-- Data Table Card -->
      <div class="bg-white/70 backdrop-blur-md rounded-xl shadow-sm border border-white/20 overflow-hidden relative">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low/50 border-b border-outline-variant text-on-surface-variant text-label-sm font-label-sm uppercase tracking-wider">
                <th class="py-4 px-6 font-semibold">ID Transaksi</th>
                <th class="py-4 px-6 font-semibold">Tanggal</th>
                <th class="py-4 px-6 font-semibold">Pelanggan</th>
                <th class="py-4 px-6 font-semibold">Kendaraan</th>
                <th class="py-4 px-6 font-semibold">Total Harga</th>
                <th class="py-4 px-6 font-semibold text-center">Status</th>
                <th class="py-4 px-6 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="text-body-md font-body-md divide-y divide-outline-variant/20">
              <tr class="hover:bg-surface-container-lowest transition-colors">
                <td class="py-4 px-6 font-semibold text-primary">TRX-98231</td>
                <td class="py-4 px-6 text-on-surface-variant">12 Okt 2024</td>
                <td class="py-4 px-6">Budi Santoso</td>
                <td class="py-4 px-6">Toyota Avanza 2023</td>
                <td class="py-4 px-6">Rp 1.500.000</td>
                <td class="py-4 px-6 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                    Selesai
                  </span>
                </td>
                <td class="py-4 px-6 text-right">
                  <button class="text-secondary hover:text-secondary-container transition-colors p-1" title="Detail">
                    <span class="material-symbols-outlined text-[20px]" data-icon="visibility">visibility</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-outline-variant/30 bg-surface-container-lowest/50 rounded-b-xl">
          <span class="text-label-sm font-label-sm text-on-surface-variant">Menampilkan 1 dari 45 transaksi</span>
          <div class="flex gap-2">
            <button class="p-1.5 rounded border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-colors disabled:opacity-50" disabled>
              <span class="material-symbols-outlined text-sm" data-icon="chevron_left">chevron_left</span>
            </button>
            <button class="w-8 h-8 rounded bg-secondary text-on-secondary font-label-sm font-bold flex items-center justify-center">1</button>
            <button class="p-1.5 rounded border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-colors">
              <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>
