<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Manajemen Transaksi</h1>
        <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Pantau seluruh alur pemesanan dan riwayat sewa kendaraan.</p>
      </div>
      <button @click="exportToExcel" class="bg-secondary text-on-secondary px-5 py-2.5 rounded-lg font-bold text-label-md
                     flex items-center gap-2 shadow-sm hover:bg-secondary-container transition-colors disabled:opacity-50" :disabled="exporting">
        <span v-if="exporting" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
        <span v-else class="material-symbols-outlined" style="font-size:18px">download</span>
        {{ exporting ? 'Mengekspor...' : 'Export Excel' }}
      </button>
    </div>

    <!-- Filters -->
    <div class="filter-panel mb-6 flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
      <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
        <!-- Search Field -->
        <div class="relative w-full sm:w-72">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary dark:text-blue-400 pointer-events-none transition-colors" style="font-size:20px">search</span>
          <input v-model="search" type="text" placeholder="Cari ID, pelanggan, mobil..."
            class="search-input-field" />
          <button v-if="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <span class="material-symbols-outlined" style="font-size:18px">close</span>
          </button>
        </div>

        <!-- Date Range -->
        <div class="flex items-center gap-2">
          <input type="date" v-model="filterMulai" title="Tanggal Mulai"
            class="form-input py-2 text-sm w-36" />
          <span class="text-slate-400 dark:text-slate-500">-</span>
          <input type="date" v-model="filterAkhir" title="Tanggal Akhir"
            class="form-input py-2 text-sm w-36" />
        </div>

        <!-- Status Filter -->
        <select v-model="filterStatus"
          class="form-input py-2 text-sm w-40">
          <option value="">Semua Status</option>
          <option value="selesai">Selesai</option>
          <option value="menunggu">Menunggu</option>
          <option value="aktif">Aktif</option>
          <option value="dibatalkan">Dibatalkan</option>
        </select>
      </div>

      <div v-if="filterMulai || filterAkhir || filterStatus || search" class="flex justify-end">
        <button @click="resetFilters" class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 flex items-center gap-1">
          <span class="material-symbols-outlined" style="font-size:16px">restart_alt</span>
          Reset Filter
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-panel">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700/80 text-slate-600 dark:text-slate-300 text-label-sm font-label-sm uppercase tracking-wider">
              <th class="py-4 px-6 font-semibold">ID Transaksi</th>
              <th class="py-4 px-6 font-semibold">Tanggal Sewa</th>
              <th class="py-4 px-6 font-semibold">Pelanggan</th>
              <th class="py-4 px-6 font-semibold">Kendaraan</th>
              <th class="py-4 px-6 font-semibold">Total</th>
              <th class="py-4 px-6 font-semibold text-center">Status</th>
              <th class="py-4 px-6 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-body-md font-body-md divide-y divide-slate-100 dark:divide-slate-700/50">
            <tr v-if="loading">
              <td colspan="7" class="py-10 text-center text-slate-500 dark:text-slate-400">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
                <p>Memuat data transaksi...</p>
              </td>
            </tr>
            <tr v-else-if="filteredTransaksi.length === 0">
              <td colspan="7" class="py-10 text-center text-slate-500 dark:text-slate-400">
                <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">receipt_long</span>
                Belum ada data transaksi ditemukan.
              </td>
            </tr>
            <tr v-else v-for="t in filteredTransaksi" :key="t.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-750/50 transition-colors">
              <td class="py-4 px-6 font-semibold font-mono text-sm text-secondary dark:text-blue-400">#{{ t.id }}</td>
              <td class="py-4 px-6 text-slate-600 dark:text-slate-400 text-sm whitespace-nowrap">{{ formatDate(t.tanggal_mulai) }} - {{ formatDate(t.tanggal_akhir) }}</td>
              <td class="py-4 px-6 font-medium text-slate-900 dark:text-slate-100">{{ t.pelanggan?.nama || '-' }}</td>
              <td class="py-4 px-6 text-slate-700 dark:text-slate-300">{{ t.kendaraan?.nama || '-' }}</td>
              <td class="py-4 px-6 font-semibold text-slate-900 dark:text-slate-100">{{ formatRupiah(t.total) }}</td>
              <td class="py-4 px-6 text-center">
                <StatusBadge :status="t.status" />
              </td>
              <td class="py-4 px-6 text-right">
                <button @click="openDetail(t)" title="Lihat Detail"
                  class="text-secondary dark:text-blue-400 hover:text-secondary-container p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                  <span class="material-symbols-outlined" style="font-size:20px">visibility</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-900/60 gap-3">
        <span class="text-label-sm font-label-sm text-slate-500 dark:text-slate-400">
          Menampilkan {{ pagination?.from || 0 }}-{{ pagination?.to || 0 }} dari {{ pagination?.total || 0 }}
        </span>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-secondary dark:text-blue-400 font-bold text-label-md hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Prev
          </button>
          <span class="px-3 py-1.5 rounded-lg bg-secondary text-on-secondary font-bold text-label-md">
            {{ currentPage }}
          </span>
          <button @click="currentPage++" :disabled="currentPage === pagination?.last_page || !pagination?.last_page"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-secondary dark:text-blue-400 font-bold text-label-md hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Detail Transaksi -->
    <BaseModal v-model="showDetail" max-width="720px">
      <template #header>
        <div class="flex items-center gap-3">
          <h2 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white">Detail Transaksi</h2>
          <StatusBadge v-if="selected" :status="selected.status" />
          <span class="text-label-md font-mono text-slate-400 dark:text-slate-500">#{{ selected?.id }}</span>
        </div>
      </template>

      <div v-if="selected" class="flex flex-col gap-6">
        <!-- 2-col info cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-slate-50 dark:bg-slate-900/70 rounded-xl p-5 border border-slate-200 dark:border-slate-700 flex gap-4 items-start">
            <div class="w-16 h-16 rounded-lg bg-slate-200 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500 dark:text-slate-400">
              <span class="material-symbols-outlined text-3xl">directions_car</span>
            </div>
            <div class="min-w-0">
              <p class="text-label-sm font-label-sm text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Informasi Kendaraan</p>
              <p class="text-body-lg font-semibold text-slate-900 dark:text-white truncate">{{ selected.kendaraan?.nama }}</p>
              <p class="text-label-md text-slate-500 dark:text-slate-400">{{ selected.kendaraan?.tipe }} · {{ selected.kendaraan?.plat }}</p>
            </div>
          </div>
          <div class="bg-slate-50 dark:bg-slate-900/70 rounded-xl p-5 border border-slate-200 dark:border-slate-700 flex gap-4 items-start">
            <div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900/60 text-secondary dark:text-blue-300
                        flex items-center justify-center font-bold text-lg shrink-0">
              {{ initials(selected.pelanggan?.nama) }}
            </div>
            <div class="min-w-0">
              <p class="text-label-sm font-label-sm text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Informasi Pelanggan</p>
              <p class="text-body-lg font-semibold text-slate-900 dark:text-white truncate">{{ selected.pelanggan?.nama }}</p>
              <p class="text-label-md text-slate-500 dark:text-slate-400 flex items-center gap-1 truncate">
                <span class="material-symbols-outlined" style="font-size:14px">mail</span>
                {{ selected.pelanggan?.email }}
              </p>
            </div>
          </div>
        </div>

        <!-- Detail pemesanan -->
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl p-5">
          <h3 class="text-body-md font-bold text-slate-900 dark:text-white mb-4">Detail Pemesanan</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <p class="text-label-sm font-label-sm text-slate-400 dark:text-slate-500 mb-1">Tanggal Sewa</p>
              <p class="text-label-md font-medium text-slate-800 dark:text-slate-200">{{ formatDate(selected.tanggal_mulai) }}</p>
            </div>
            <div>
              <p class="text-label-sm font-label-sm text-slate-400 dark:text-slate-500 mb-1">Tanggal Kembali</p>
              <p class="text-label-md font-medium text-slate-800 dark:text-slate-200">{{ formatDate(selected.tanggal_akhir) }}</p>
            </div>
            <div>
              <p class="text-label-sm font-label-sm text-slate-400 dark:text-slate-500 mb-1">Durasi</p>
              <p class="text-label-md font-medium text-slate-800 dark:text-slate-200">{{ getDurasi(selected.tanggal_mulai, selected.tanggal_akhir) }}</p>
            </div>
            <div>
              <p class="text-label-sm font-label-sm text-slate-400 dark:text-slate-500 mb-1">Total Biaya</p>
              <p class="text-label-md font-bold text-secondary dark:text-blue-400">{{ formatRupiah(selected.total) }}</p>
            </div>
          </div>
        </div>

        <!-- Stepper status -->
        <div>
          <h3 class="text-body-md font-bold text-slate-900 dark:text-white mb-6">Status Pemesanan</h3>
          <div class="relative flex items-center justify-between w-full pb-4">
            <div class="absolute top-4 left-4 right-4 h-0.5 bg-slate-200 dark:bg-slate-700 -z-10"></div>
            <div class="absolute top-4 left-4 h-0.5 bg-secondary dark:bg-blue-500 -z-10 transition-all"
              :style="{ width: stepProgress }"></div>
            <div v-for="(step, i) in steps" :key="step"
              class="flex flex-col items-center gap-2 relative z-10">
              <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-sm text-xs font-bold transition-all"
                :class="i < currentStep
                  ? 'bg-secondary dark:bg-blue-600 text-white'
                  : i === currentStep
                    ? 'border-2 border-secondary dark:border-blue-400 bg-white dark:bg-slate-800 text-secondary dark:text-blue-400'
                    : 'bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 text-slate-400 dark:text-slate-500'">
                <span v-if="i < currentStep" class="material-symbols-outlined" style="font-size:18px">check</span>
                <div v-else-if="i === currentStep" class="w-2.5 h-2.5 rounded-full bg-secondary dark:bg-blue-400"></div>
                <span v-else>{{ i + 1 }}</span>
              </div>
              <span class="text-label-sm font-label-sm text-center"
                :class="i === currentStep ? 'text-secondary dark:text-blue-400 font-bold' : i < currentStep ? 'text-slate-800 dark:text-slate-200 font-medium' : 'text-slate-400 dark:text-slate-500'">
                {{ step }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <button @click="showDetail = false"
          class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600
                 text-label-md font-semibold transition-colors">
          Tutup
        </button>
        <button v-if="selected?.status === 'aktif'" @click="tandaiKembali"
          class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700
                 text-label-md font-bold shadow-sm flex items-center gap-2 transition-colors">
          <span class="material-symbols-outlined" style="font-size:18px">task_alt</span>
          Tandai Dikembalikan
        </button>
        <template v-if="selected?.status === 'menunggu'">
          <button @click="tolakTransaksi"
            class="px-5 py-2.5 rounded-lg border border-rose-500 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40
                   text-label-md font-semibold transition-colors">
            Tolak
          </button>
          <button @click="konfirmasiTransaksi"
            class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary hover:bg-secondary-container
                   text-label-md font-bold shadow-sm transition-colors">
            Konfirmasi
          </button>
        </template>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useToastStore } from '@/stores/toast'
import { useSearchStore } from '@/stores/search'
import api from '@/services/api'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import BaseModal   from '@/components/ui/BaseModal.vue'

const toast = useToastStore()
const searchStore = useSearchStore()

const search       = ref('')
const filterMulai  = ref('')
const filterAkhir  = ref('')
const filterStatus = ref('')
const showDetail   = ref(false)
const selected     = ref(null)

const transaksi    = ref([])
const loading      = ref(false)
const currentPage  = ref(1)
const pagination   = ref({})

const steps       = ['Menunggu', 'Dikonfirmasi', 'Aktif', 'Dikembalikan']
const statusToStep = { menunggu:0, pending:0, aktif:2, selesai:3, dikembalikan:3, dibatalkan:0 }
const currentStep  = computed(() => statusToStep[selected.value?.status?.toLowerCase()] ?? 0)
const stepProgress = computed(() => {
  const pct = (currentStep.value / (steps.length - 1)) * 100
  return `calc(${pct}% - 16px)`
})

function resetFilters() {
  search.value = ''
  filterMulai.value = ''
  filterAkhir.value = ''
  filterStatus.value = ''
  searchStore.setQuery('')
}

const filteredTransaksi = computed(() => {
  const q = (search.value || searchStore.query || '').toLowerCase().trim()
  if (!q) return transaksi.value
  return transaksi.value.filter(t => 
    String(t.id).toLowerCase().includes(q) ||
    t.pelanggan?.nama?.toLowerCase().includes(q) ||
    t.kendaraan?.nama?.toLowerCase().includes(q) ||
    t.kendaraan?.plat?.toLowerCase().includes(q)
  )
})

async function fetchTransaksi() {
  loading.value = true
  try {
    const response = await api.get('/transaksi', {
      params: {
        status: filterStatus.value,
        tanggal_mulai: filterMulai.value,
        tanggal_akhir: filterAkhir.value,
        page: currentPage.value
      }
    })
    transaksi.value = response.data.data
    pagination.value = response.data
  } catch (error) {
    console.error('Error fetching transaksi:', error)
    toast.error('Gagal', 'Tidak dapat memuat data transaksi')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTransaksi()
})

let debounceTimer
watch([filterMulai, filterAkhir, filterStatus], () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    currentPage.value = 1
    fetchTransaksi()
  }, 300)
})

watch(currentPage, () => {
  fetchTransaksi()
})

function initials(name) {
  return name?.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase() || ''
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(date)
}

function getDurasi(start, end) {
  if (!start || !end) return '-'
  const s = new Date(start)
  const e = new Date(end)
  const diffTime = Math.abs(e - s)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return `${diffDays} Hari`
}

function openDetail(t) { 
  selected.value = t
  showDetail.value = true 
}

function formatRupiah(n) { 
  if (!n) return 'Rp 0'
  return 'Rp ' + Number(n).toLocaleString('id-ID') 
}

async function tandaiKembali() {
  try {
    await api.patch(`/transaksi/${selected.value.id}/selesai`)
    toast.success('Berhasil', 'Kendaraan telah ditandai dikembalikan.')
    showDetail.value = false
    fetchTransaksi()
  } catch (error) {
    console.error('Error finishing transaksi:', error)
    toast.error('Gagal', 'Gagal menyelesaikan transaksi')
  }
}

async function konfirmasiTransaksi() {
  try {
    await api.patch(`/transaksi/${selected.value.id}/konfirmasi`)
    toast.success('Dikonfirmasi', 'Transaksi berhasil dikonfirmasi.')
    showDetail.value = false
    fetchTransaksi()
  } catch (error) {
    console.error('Error confirming transaksi:', error)
    toast.error('Gagal', 'Gagal mengkonfirmasi transaksi')
  }
}

async function tolakTransaksi() {
  try {
    await api.patch(`/transaksi/${selected.value.id}`, { status: 'dibatalkan' })
    toast.error('Ditolak', 'Transaksi telah ditolak.')
    showDetail.value = false
    fetchTransaksi()
  } catch (error) {
    console.error('Error rejecting transaksi:', error)
    toast.error('Gagal', 'Gagal menolak transaksi')
  }
}

const exporting = ref(false)
async function exportToExcel() {
  exporting.value = true
  try {
    const response = await api.get('/transaksi', {
      params: {
        status: filterStatus.value,
        tanggal_mulai: filterMulai.value,
        tanggal_akhir: filterAkhir.value,
        limit: 1000
      }
    })
    
    let dataToExport = response.data.data || response.data
    
    const headers = ['ID Transaksi', 'Tanggal Mulai', 'Tanggal Akhir', 'Pelanggan', 'Kendaraan', 'Total', 'Status']
    const rows = dataToExport.map(t => [
      t.id,
      t.tanggal_mulai,
      t.tanggal_akhir,
      `"${t.pelanggan?.nama || '-'}"`,
      `"${t.kendaraan?.nama || '-'}"`,
      t.total,
      t.status
    ])
    
    const csvContent = [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `transaksi_export_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    toast.success('Berhasil', 'Data transaksi berhasil diekspor')
  } catch (error) {
    console.error('Export error:', error)
    toast.error('Gagal', 'Gagal mengekspor data')
  } finally {
    exporting.value = false
  }
}
</script>
