<template>
  <div>
    <!-- Welcome header -->
    <div class="flex justify-between items-end mb-10">
      <div>
        <h1 class="text-headline-lg font-headline-lg text-primary mb-2">
          Selamat Datang di Dashboard
        </h1>
        <p class="text-body-md font-body-md text-on-surface-variant">
          Berikut ringkasan kinerja, pelanggan, dan aktivitas transaksi armada Anda.
        </p>
      </div>
      <button class="flex items-center gap-2 bg-secondary text-on-secondary font-bold
                     text-label-md py-2 px-4 rounded-lg hover:bg-secondary-container
                     transition-colors shadow-md">
        <span class="material-symbols-outlined" style="font-size:18px">download</span>
        Cetak Laporan
      </button>
    </div>

    <!-- Stats grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-12">
      <!-- Card: Pendapatan -->
      <div class="glass-card rounded-xl p-6 shadow-[0px_4px_20px_rgba(15,23,42,0.05)] relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary-fixed/30 rounded-full blur-xl
                    group-hover:bg-primary-fixed/50 transition-all"></div>
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-secondary-fixed flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined">payments</span>
          </div>
          <span class="text-label-md font-label-md text-on-surface-variant">Total Pendapatan</span>
        </div>
        <div class="text-headline-md font-headline-md text-primary">
          {{ loading ? '...' : formatRupiah(stats?.total_pendapatan) }}
        </div>
        <div class="mt-2 text-label-sm font-label-sm text-secondary flex items-center gap-1">
          <span class="material-symbols-outlined" style="font-size:14px">trending_up</span>
          +15% dari bulan lalu
        </div>
      </div>

      <!-- Card: Kendaraan -->
      <div class="glass-card rounded-xl p-6 shadow-[0px_4px_20px_rgba(15,23,42,0.05)] relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-error-container/30 rounded-full blur-xl
                    group-hover:bg-error-container/50 transition-all"></div>
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface">
            <span class="material-symbols-outlined">directions_car</span>
          </div>
          <span class="text-label-md font-label-md text-on-surface-variant">Kendaraan Disewa</span>
        </div>
        <div class="text-headline-md font-headline-md text-primary">
          <span v-if="loading">...</span>
          <span v-else>{{ stats?.kendaraan_disewa }} / {{ stats?.total_kendaraan }}</span>
        </div>
        <div class="mt-2 text-label-sm font-label-sm text-on-surface-variant flex items-center gap-1">
          Sedang digunakan hari ini
        </div>
      </div>

      <!-- Card: Pengguna -->
      <div class="glass-card rounded-xl p-6 shadow-[0px_4px_20px_rgba(15,23,42,0.05)] relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-secondary-fixed-dim/30 rounded-full blur-xl
                    group-hover:bg-secondary-fixed-dim/50 transition-all"></div>
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-primary-fixed-dim flex items-center justify-center text-primary-container">
            <span class="material-symbols-outlined">group_add</span>
          </div>
          <span class="text-label-md font-label-md text-on-surface-variant">Pengguna Baru</span>
        </div>
        <div class="text-headline-md font-headline-md text-primary">
          {{ loading ? '...' : stats?.pengguna_baru }}
        </div>
        <div class="mt-2 text-label-sm font-label-sm text-secondary flex items-center gap-1">
          <span class="material-symbols-outlined" style="font-size:14px">trending_up</span>
          +8% minggu ini
        </div>
      </div>

      <!-- Card: Quick action -->
      <div class="glass-card rounded-xl p-6 shadow-[0px_4px_20px_rgba(15,23,42,0.05)] relative overflow-hidden group flex flex-col justify-between">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-secondary-fixed/30 rounded-full blur-xl
                    group-hover:bg-secondary-fixed/50 transition-all"></div>
        <div>
          <h3 class="text-headline-md font-headline-md text-primary mb-2">Transaksi Cepat</h3>
          <p class="text-body-md font-body-md text-on-surface-variant">
            Buat pesanan baru untuk pelanggan secara langsung.
          </p>
        </div>
        <RouterLink to="/transaksi"
          class="mt-4 w-full bg-secondary text-on-secondary font-bold text-label-md py-3
                 rounded-lg hover:bg-secondary-container transition-colors shadow-md text-center relative z-10 block">
          + Buat Pesanan
        </RouterLink>
      </div>
    </div>

    <!-- Recent activity -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0px_4px_20px_rgba(15,23,42,0.05)] overflow-hidden">
      <div class="p-6 border-b border-outline-variant flex justify-between items-center">
        <h2 class="text-headline-md font-headline-md text-primary">Aktivitas Terbaru</h2>
        <RouterLink to="/transaksi"
          class="text-secondary text-label-md font-label-md hover:underline">
          Lihat Semua
        </RouterLink>
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
          <tbody class="divide-y divide-outline-variant/30">
            <tr v-if="loading">
              <td colspan="5" class="p-4 text-center py-8">
                <span class="material-symbols-outlined animate-spin text-primary" style="font-size: 32px;">progress_activity</span>
              </td>
            </tr>
            <tr v-else-if="stats?.transaksi_terbaru?.length === 0">
              <td colspan="5" class="p-4 text-center py-8 text-on-surface-variant">Belum ada transaksi</td>
            </tr>
            <tr v-else v-for="item in stats?.transaksi_terbaru" :key="item.id"
              class="hover:bg-surface-container-low/50 transition-colors">
              <td class="p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-md bg-surface-variant flex items-center justify-center">
                  <span class="material-symbols-outlined text-outline">directions_car</span>
                </div>
                <span class="font-semibold text-primary">{{ item.kendaraan?.nama }}</span>
              </td>
              <td class="p-4 text-on-surface-variant">{{ item.pelanggan?.nama }}</td>
              <td class="p-4 text-on-surface-variant">{{ item.tanggal_mulai }} - {{ item.tanggal_akhir }}</td>
              <td class="p-4">
                <StatusBadge :status="item.status" />
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import StatusBadge from '@/components/ui/StatusBadge.vue'

const stats = ref(null)
const loading = ref(true)

function formatRupiah(n) {
  if (!n) return 'Rp 0'
  if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1) + 'M'
  return 'Rp ' + Number(n).toLocaleString('id-ID')
}

async function fetchStats() {
  loading.value = true
  try {
    const { data } = await api.get('/dashboard/stats')
    stats.value = data
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>
