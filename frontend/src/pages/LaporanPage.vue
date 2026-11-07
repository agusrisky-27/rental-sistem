<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Laporan Pendapatan {{ tahun }}</h1>
        <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Pendapatan sewa dan denda per bulan (hanya pembayaran berhasil).</p>
      </div>
      <div class="flex flex-wrap items-center gap-3 print:hidden">
        <select v-model.number="tahun" class="form-input py-2.5 w-28">
          <option v-for="y in tahunList" :key="y" :value="y">{{ y }}</option>
        </select>
        <button @click="exportCsv" :disabled="exporting"
          class="bg-secondary text-on-secondary px-5 py-2.5 rounded-lg font-bold text-label-md flex items-center gap-2 shadow-sm hover:bg-secondary-container transition-colors disabled:opacity-50">
          <span class="material-symbols-outlined icon-18">download</span>
          {{ exporting ? 'Mengekspor...' : 'Export CSV' }}
        </button>
        <button @click="cetak"
          class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-label-md font-semibold flex items-center gap-2 transition-colors">
          <span class="material-symbols-outlined icon-18">print</span>
          Cetak
        </button>
      </div>
    </div>

    <!-- Bar chart -->
    <div class="glass-card rounded-xl p-6 shadow-sm mb-6">
      <h2 class="text-body-lg font-bold text-slate-900 dark:text-white mb-4">Total Pendapatan per Bulan</h2>
      <div class="flex items-end gap-2 h-48">
        <div v-for="r in rows" :key="r.bulan" class="flex-1 flex flex-col items-center justify-end h-full gap-1">
          <div class="w-full rounded-t bg-secondary dark:bg-blue-500 transition-all"
            :style="{ height: barHeight(r.total) }" :title="formatRupiah(r.total)"></div>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ bulanNama[r.bulan - 1].slice(0, 3) }}</span>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-panel">
      <div v-if="loading" class="py-10 text-center text-slate-500 dark:text-slate-400">
        <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
        <p>Memuat laporan...</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700/80 text-slate-600 dark:text-slate-300 text-label-sm font-label-sm uppercase tracking-wider">
              <th class="py-4 px-6 font-semibold">Bulan</th>
              <th class="py-4 px-6 font-semibold text-right">Pendapatan Sewa</th>
              <th class="py-4 px-6 font-semibold text-right">Pendapatan Denda</th>
              <th class="py-4 px-6 font-semibold text-right">Total</th>
              <th class="py-4 px-6 font-semibold text-right">Jumlah Transaksi</th>
            </tr>
          </thead>
          <tbody class="text-body-md divide-y divide-slate-100 dark:divide-slate-700/50">
            <tr v-for="r in rows" :key="r.bulan">
              <td class="py-3 px-6 font-medium text-slate-900 dark:text-slate-100">{{ bulanNama[r.bulan - 1] }}</td>
              <td class="py-3 px-6 text-right text-slate-700 dark:text-slate-300">{{ formatRupiah(r.sewa) }}</td>
              <td class="py-3 px-6 text-right text-slate-700 dark:text-slate-300">{{ formatRupiah(r.denda) }}</td>
              <td class="py-3 px-6 text-right font-semibold text-slate-900 dark:text-slate-100">{{ formatRupiah(r.total) }}</td>
              <td class="py-3 px-6 text-right text-slate-700 dark:text-slate-300">{{ r.jumlah_transaksi }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-slate-50 dark:bg-slate-900/80 border-t-2 border-slate-200 dark:border-slate-700 font-bold text-slate-900 dark:text-white">
              <td class="py-3 px-6">TOTAL</td>
              <td class="py-3 px-6 text-right">{{ formatRupiah(jumlah.sewa) }}</td>
              <td class="py-3 px-6 text-right">{{ formatRupiah(jumlah.denda) }}</td>
              <td class="py-3 px-6 text-right">{{ formatRupiah(jumlah.total) }}</td>
              <td class="py-3 px-6 text-right">{{ jumlah.jumlah_transaksi }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'

const toast = useToastStore()

const bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

const now = new Date().getFullYear()
const tahunList = Array.from({ length: 5 }, (_, i) => now - i)
const tahun = ref(now)
const rows = ref(bulanNama.map((_, i) => ({ bulan: i + 1, sewa: 0, denda: 0, total: 0, jumlah_transaksi: 0 })))
const loading = ref(false)
const exporting = ref(false)

const jumlah = computed(() => rows.value.reduce((a, r) => ({
  sewa: a.sewa + Number(r.sewa),
  denda: a.denda + Number(r.denda),
  total: a.total + Number(r.total),
  jumlah_transaksi: a.jumlah_transaksi + Number(r.jumlah_transaksi),
}), { sewa: 0, denda: 0, total: 0, jumlah_transaksi: 0 }))

const maxTotal = computed(() => Math.max(...rows.value.map(r => Number(r.total)), 0))
const barHeight = (n) => maxTotal.value ? `${Math.max((Number(n) / maxTotal.value) * 100, n > 0 ? 2 : 0)}%` : '0%'

function formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID') }

async function fetchLaporan() {
  loading.value = true
  try {
    const res = await api.get('/laporan/pendapatan', { params: { tahun: tahun.value } })
    rows.value = res.data.data
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Gagal memuat laporan.')
  } finally {
    loading.value = false
  }
}

async function exportCsv() {
  exporting.value = true
  try {
    const res = await api.get('/laporan/pendapatan/export', {
      params: { tahun: tahun.value },
      responseType: 'blob',
    })
    const url = URL.createObjectURL(res.data)
    const link = document.createElement('a')
    link.href = url
    link.download = `laporan_pendapatan_${tahun.value}.csv`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
    toast.success('Berhasil', 'Laporan berhasil diekspor.')
  } catch (error) {
    toast.error('Gagal', 'Gagal mengekspor laporan.')
  } finally {
    exporting.value = false
  }
}

const cetak = () => window.print()

watch(tahun, fetchLaporan)
onMounted(fetchLaporan)
</script>
