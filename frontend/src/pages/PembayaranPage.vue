<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Manajemen Pembayaran</h1>
        <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Verifikasi dan kelola transaksi pembayaran pelanggan.</p>
      </div>
      <div class="flex gap-3">
        <button class="bg-secondary text-on-secondary px-5 py-2.5 rounded-lg text-label-md font-bold
                       hover:bg-secondary-container flex items-center gap-2 shadow-sm transition-colors">
          <span class="material-symbols-outlined" style="font-size:18px">download</span>
          Export Laporan
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div v-for="stat in stats" :key="stat.label"
        class="glass-card rounded-xl p-6 relative overflow-hidden transition-all shadow-sm">
        <div class="flex justify-between items-start mb-4">
          <p class="text-label-md font-label-md text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
          <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="stat.iconBg">
            <span class="material-symbols-outlined" :class="stat.iconColor">{{ stat.icon }}</span>
          </div>
        </div>
        <h3 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">{{ stat.value }}</h3>
        <p class="text-label-sm font-label-sm mt-2 flex items-center gap-1" :class="stat.trendColor">
          <span class="material-symbols-outlined" style="font-size:14px">{{ stat.trendIcon }}</span>
          {{ stat.trend }}
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="filter-panel mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="flex flex-wrap gap-3 w-full md:w-auto">
        <!-- Search -->
        <div class="relative w-full md:w-80">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary dark:text-blue-400 pointer-events-none transition-colors" style="font-size:20px">search</span>
          <input v-model="search" type="text" placeholder="Cari pelanggan, ID transaksi..."
            class="search-input-field" />
          <button v-if="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <span class="material-symbols-outlined" style="font-size:18px">close</span>
          </button>
        </div>

        <!-- Status Filter -->
        <select v-model="filterStatus"
          class="form-input md:w-48 py-2.5">
          <option value="">Semua Status</option>
          <option value="menunggu verifikasi">Menunggu Verifikasi</option>
          <option value="berhasil">Berhasil</option>
          <option value="ditolak">Ditolak</option>
        </select>
      </div>

      <div v-if="search || filterStatus" class="flex justify-end w-full md:w-auto">
        <button @click="search = ''; filterStatus = ''" class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 flex items-center gap-1">
          <span class="material-symbols-outlined" style="font-size:16px">restart_alt</span>
          Reset
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-panel">
      <div class="p-5 border-b border-slate-200 dark:border-slate-700/80 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/60">
        <h2 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white">Daftar Pembayaran</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/80 text-slate-600 dark:text-slate-300 text-label-md font-label-md border-b border-slate-200 dark:border-slate-700/80">
              <th class="py-4 px-6 font-semibold">ID Transaksi</th>
              <th class="py-4 px-6 font-semibold">Pelanggan</th>
              <th class="py-4 px-6 font-semibold">Metode</th>
              <th class="py-4 px-6 font-semibold">Jumlah</th>
              <th class="py-4 px-6 font-semibold">Status</th>
              <th class="py-4 px-6 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-body-md divide-y divide-slate-100 dark:divide-slate-700/50">
            <tr v-if="loading">
              <td colspan="6" class="text-center py-10 text-slate-500 dark:text-slate-400">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
                <p>Memuat data pembayaran...</p>
              </td>
            </tr>
            <tr v-else-if="filteredPembayaran.length === 0">
              <td colspan="6" class="text-center py-10 text-slate-500 dark:text-slate-400">
                <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">payments</span>
                Tidak ada data pembayaran ditemukan.
              </td>
            </tr>
            <tr v-for="p in filteredPembayaran" :key="p.id"
              class="hover:bg-slate-50/80 dark:hover:bg-slate-750/50 transition-colors group">
              <td class="py-4 px-6 font-semibold font-mono text-sm text-secondary dark:text-blue-400">#{{ p.id }}</td>
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/60 text-secondary dark:text-blue-300
                              flex items-center justify-center font-bold text-label-sm shrink-0">
                    {{ initials(p.transaksi?.pelanggan?.nama || '') }}
                  </div>
                  <div>
                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ p.transaksi?.pelanggan?.nama || '-' }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ p.transaksi?.pelanggan?.email || '-' }}</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6 text-slate-700 dark:text-slate-300">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-secondary dark:text-blue-400" style="font-size:20px">{{ metodeIcon(p.metode || '') }}</span>
                  <span>{{ p.metode }}</span>
                </div>
              </td>
              <td class="py-4 px-6 font-semibold text-slate-900 dark:text-slate-100">{{ formatRupiah(p.jumlah) }}</td>
              <td class="py-4 px-6">
                <StatusBadge :status="p.status" />
              </td>
              <td class="py-4 px-6 text-right">
                <!-- Menunggu: approve/reject -->
                <div v-if="p.status === 'menunggu verifikasi'"
                  class="flex justify-end gap-1.5">
                  <button @click="tolak(p)" title="Tolak"
                    class="p-1.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px">close</span>
                  </button>
                  <button @click="setujui(p)" title="Setujui"
                    class="p-1.5 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px">check</span>
                  </button>
                </div>
                <!-- Berhasil: detail -->
                <button v-else @click="openDetail(p)"
                  class="text-secondary dark:text-blue-400 hover:text-secondary-container text-label-md font-semibold transition-colors px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-700">
                  Detail
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50 dark:bg-slate-900/60" v-if="pagination">
        <span class="text-label-md font-label-md text-slate-500 dark:text-slate-400">
          Menampilkan {{ (currentPage - 1) * pagination.per_page + 1 }}-{{ Math.min(currentPage * pagination.per_page, pagination.total) || 0 }} dari {{ pagination.total }}
        </span>
        <div class="flex gap-1">
          <button v-for="page in pagination.last_page" :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-1 rounded-md font-semibold text-sm transition-colors',
              page === currentPage ? 'bg-secondary text-on-secondary' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700'
            ]">
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Detail Pembayaran -->
    <BaseModal v-model="showDetail" max-width="560px">
      <template #header>
        <div class="flex items-center gap-3">
          <h2 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white">Detail Pembayaran</h2>
          <StatusBadge v-if="selected" :status="selected.status" />
        </div>
      </template>

      <div v-if="selected" class="flex flex-col gap-3">
        <div v-for="row in detailRows" :key="row.label"
          class="flex justify-between items-center py-2.5 border-b border-slate-100 dark:border-slate-700/60 last:border-0">
          <span class="text-label-md text-slate-500 dark:text-slate-400">{{ row.label }}</span>
          <span class="text-body-md font-semibold text-slate-900 dark:text-white">{{ row.value }}</span>
        </div>
      </div>

      <template #footer>
        <button @click="showDetail = false"
          class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 text-label-md font-semibold
                 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
          Tutup
        </button>
        <button v-if="selected?.status === 'menunggu verifikasi'" @click="konfirmasi"
          class="bg-secondary hover:bg-secondary-container text-on-secondary font-bold py-2.5 px-5
                 rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm">
          <span class="material-symbols-outlined" style="font-size:18px">check_circle</span>
          Konfirmasi Pembayaran
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useToastStore } from '@/stores/toast'
import api from '@/services/api'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import BaseModal   from '@/components/ui/BaseModal.vue'

const toast        = useToastStore()
const search       = ref('')
const filterStatus = ref('')
const showDetail   = ref(false)
const selected     = ref(null)
const loading      = ref(false)
const currentPage  = ref(1)
const pagination   = ref(null)
const pembayaran   = ref([])

const stats = [
  { label:'Menunggu Verifikasi', value:'12', icon:'pending_actions', iconBg:'bg-amber-100 dark:bg-amber-950/60',     iconColor:'text-amber-600 dark:text-amber-400',   trend:'+3 dari kemarin',       trendIcon:'arrow_upward', trendColor:'text-amber-500' },
  { label:'Berhasil (Hari ini)', value:'48', icon:'check_circle',    iconBg:'bg-emerald-100 dark:bg-emerald-950/60', iconColor:'text-emerald-600 dark:text-emerald-400', trend:'+15% dari rata-rata',   trendIcon:'arrow_upward', trendColor:'text-emerald-500'  },
  { label:'Total Pending',       value:'Rp 15.450.000', icon:'payments', iconBg:'bg-blue-100 dark:bg-blue-950/60', iconColor:'text-secondary dark:text-blue-400', trend:'Menunggu konfirmasi', trendIcon:'info',         trendColor:'text-slate-400' },
]

const fetchPembayaran = async () => {
  loading.value = true
  try {
    const response = await api.get(`/pembayaran?page=${currentPage.value}&status=${filterStatus.value}`)
    pembayaran.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    toast.error('Gagal', 'Gagal memuat data pembayaran')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPembayaran()
})

watch(filterStatus, () => {
  currentPage.value = 1
  fetchPembayaran()
})

const changePage = (page) => {
  currentPage.value = page
  fetchPembayaran()
}

const filteredPembayaran = computed(() => {
  if (!search.value) return pembayaran.value
  const q = search.value.toLowerCase()
  return pembayaran.value.filter(p => 
    String(p.id).includes(q) ||
    p.transaksi?.pelanggan?.nama?.toLowerCase().includes(q) ||
    p.metode?.toLowerCase().includes(q)
  )
})

const detailRows = computed(() => selected.value ? [
  { label: 'ID Transaksi',   value: `#${selected.value.id}` },
  { label: 'Nama Pelanggan', value: selected.value.transaksi?.pelanggan?.nama || '-' },
  { label: 'Metode',         value: selected.value.metode },
  { label: 'Jumlah',         value: formatRupiah(selected.value.jumlah) },
  { label: 'Status',         value: selected.value.status },
] : [])

function initials(name) { 
  if (!name) return ''
  return name.split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase() 
}
function formatRupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID') }
function metodeIcon(m) {
  if (m?.includes('QRIS')) return 'qr_code_scanner'
  if (m?.includes('Bank')) return 'account_balance'
  return 'payments'
}
function openDetail(p) { selected.value = p; showDetail.value = true }

async function setujui(p) {
  try {
    await api.patch(`/pembayaran/${p.id}/verifikasi`)
    toast.success('Berhasil', `Pembayaran #${p.id} dikonfirmasi.`)
    fetchPembayaran()
  } catch (error) {
    toast.error('Gagal', 'Gagal mengkonfirmasi pembayaran')
  }
}

async function tolak(p) {
  try {
    await api.patch(`/pembayaran/${p.id}`, { status: 'ditolak' })
    toast.success('Ditolak', `Pembayaran #${p.id} ditolak.`)
    fetchPembayaran()
  } catch (error) {
    toast.error('Gagal', 'Gagal menolak pembayaran')
  }
}

function konfirmasi() {
  if (selected.value) {
    setujui(selected.value)
    showDetail.value = false
  }
}
</script>
