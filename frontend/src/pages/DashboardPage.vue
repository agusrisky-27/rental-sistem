<template>
  <div>
    <!-- Welcome header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-10">
      <div>
        <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">
          Selamat Datang di Dashboard
        </h1>
        <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">
          Berikut ringkasan kinerja, ketersediaan armada, dan aktivitas transaksi terbaru Anda.
        </p>
      </div>
      <RouterLink to="/transaksi"
        class="flex items-center gap-2 bg-secondary text-on-secondary font-bold
               text-label-md py-2.5 px-5 rounded-lg hover:bg-secondary-container
               transition-colors shadow-sm">
        <span class="material-symbols-outlined" style="font-size:18px">add</span>
        Transaksi Baru
      </RouterLink>
    </div>

    <!-- Stats grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
      <!-- Card: Pendapatan -->
      <div class="glass-card rounded-xl p-6 shadow-sm relative overflow-hidden transition-all">
        <div class="flex justify-between items-start mb-4">
          <p class="text-label-md font-medium text-slate-500 dark:text-slate-400">Total Pendapatan</p>
          <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-secondary dark:text-blue-400">
            <span class="material-symbols-outlined">payments</span>
          </div>
        </div>
        <h3 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">
          {{ loading ? '...' : formatRupiah(stats?.total_pendapatan) }}
        </h3>
        <p class="text-label-sm font-label-sm mt-2 flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
          <span class="material-symbols-outlined" style="font-size:14px">trending_up</span>
          +15% dari bulan lalu
        </p>
      </div>

      <!-- Card: Kendaraan -->
      <div class="glass-card rounded-xl p-6 shadow-sm relative overflow-hidden transition-all">
        <div class="flex justify-between items-start mb-4">
          <p class="text-label-md font-medium text-slate-500 dark:text-slate-400">Armada Disewa</p>
          <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-950/60 flex items-center justify-center text-amber-600 dark:text-amber-400">
            <span class="material-symbols-outlined">directions_car</span>
          </div>
        </div>
        <h3 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">
          <span v-if="loading">...</span>
          <span v-else>{{ stats?.kendaraan_disewa || 0 }} <span class="text-sm font-normal text-slate-400">/ {{ stats?.total_kendaraan || 0 }}</span></span>
        </h3>
        <p class="text-label-sm font-label-sm mt-2 flex items-center gap-1 text-slate-500 dark:text-slate-400">
          <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
          Sedang aktif beroperasi
        </p>
      </div>

      <!-- Card: Pengguna -->
      <div class="glass-card rounded-xl p-6 shadow-sm relative overflow-hidden transition-all">
        <div class="flex justify-between items-start mb-4">
          <p class="text-label-md font-medium text-slate-500 dark:text-slate-400">Pelanggan Aktif</p>
          <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
            <span class="material-symbols-outlined">group</span>
          </div>
        </div>
        <h3 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">
          {{ loading ? '...' : (stats?.pengguna_baru || stats?.total_pelanggan || 0) }}
        </h3>
        <p class="text-label-sm font-label-sm mt-2 flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
          <span class="material-symbols-outlined" style="font-size:14px">trending_up</span>
          +8% minggu ini
        </p>
      </div>

      <!-- Card: Quick action -->
      <div class="glass-card rounded-xl p-6 shadow-sm relative overflow-hidden flex flex-col justify-between transition-all">
        <div>
          <div class="flex justify-between items-start mb-3">
            <p class="text-label-md font-medium text-slate-500 dark:text-slate-400">Aksi Cepat</p>
            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-950/60 flex items-center justify-center text-purple-600 dark:text-purple-400">
              <span class="material-symbols-outlined">bolt</span>
            </div>
          </div>
          <p class="text-sm text-slate-500 dark:text-slate-400 leading-tight mb-4">
            Kelola pengembalian kendaraan hari ini.
          </p>
        </div>
        <RouterLink to="/pengembalian"
          class="w-full bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold text-label-md py-2.5
                 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-center block border border-slate-200 dark:border-slate-700">
          Lihat Pengembalian
        </RouterLink>
      </div>
    </div>

    <!-- Recent activity -->
    <div class="table-panel">
      <div class="p-5 border-b border-slate-200 dark:border-slate-700/80 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/60">
        <div>
          <h2 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white">Aktivitas Terbaru</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Transaksi dan pemesanan rental yang baru masuk</p>
        </div>
        <RouterLink to="/transaksi"
          class="text-secondary dark:text-blue-400 text-sm font-semibold hover:underline flex items-center gap-1">
          <span>Lihat Semua</span>
          <span class="material-symbols-outlined" style="font-size:16px">arrow_forward</span>
        </RouterLink>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/80 text-slate-600 dark:text-slate-300 text-label-sm font-label-sm uppercase tracking-wider border-b border-slate-200 dark:border-slate-700/80">
              <th class="p-4 font-semibold">Kendaraan</th>
              <th class="p-4 font-semibold">Pelanggan</th>
              <th class="p-4 font-semibold">Jadwal Sewa</th>
              <th class="p-4 font-semibold">Status</th>
              <th class="p-4 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-body-md font-body-md">
            <tr v-if="loading">
              <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
                <p>Memuat aktivitas terbaru...</p>
              </td>
            </tr>
            <tr v-else-if="!stats?.transaksi_terbaru || stats?.transaksi_terbaru?.length === 0">
              <td colspan="5" class="p-10 text-center text-slate-500 dark:text-slate-400">
                <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">history</span>
                Belum ada aktivitas transaksi
              </td>
            </tr>
            <tr v-else v-for="item in stats?.transaksi_terbaru" :key="item.id"
              class="hover:bg-slate-50/80 dark:hover:bg-slate-750/50 transition-colors">
              <td class="p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                  <span class="material-symbols-outlined" style="font-size:20px">directions_car</span>
                </div>
                <span class="font-semibold text-slate-900 dark:text-white">{{ item.kendaraan?.nama || '-' }}</span>
              </td>
              <td class="p-4 text-slate-700 dark:text-slate-300">{{ item.pelanggan?.nama || '-' }}</td>
              <td class="p-4 text-slate-500 dark:text-slate-400 text-sm whitespace-nowrap">{{ item.tanggal_mulai }} - {{ item.tanggal_akhir }}</td>
              <td class="p-4">
                <StatusBadge :status="item.status" />
              </td>
              <td class="p-4 text-right">
                <RouterLink to="/transaksi" class="p-1.5 rounded-lg text-slate-400 hover:text-secondary dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 inline-flex transition-colors">
                  <span class="material-symbols-outlined" style="font-size:18px">visibility</span>
                </RouterLink>
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
