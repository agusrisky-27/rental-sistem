<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Manajemen Pengembalian</h1>
        <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Kelola dan periksa kondisi kendaraan yang dikembalikan pelanggan.</p>
      </div>
      <!-- Quick stats -->
      <div class="flex gap-4">
        <div class="glass-card p-4 rounded-xl flex items-center gap-4 shadow-sm">
          <div class="w-11 h-11 rounded-full bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center text-rose-600 dark:text-rose-400">
            <span class="material-symbols-outlined">warning</span>
          </div>
          <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Terlambat</p>
            <p class="text-headline-md font-headline-md font-bold text-rose-600 dark:text-rose-400">{{ terlambatCount }}</p>
          </div>
        </div>
        <div class="glass-card p-4 rounded-xl flex items-center gap-4 shadow-sm">
          <div class="w-11 h-11 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-secondary dark:text-blue-400">
            <span class="material-symbols-outlined">today</span>
          </div>
          <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Hari Ini</p>
            <p class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white">{{ hariIniCount }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filter-panel mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="flex flex-wrap gap-3 w-full md:w-auto">
        <!-- Search -->
        <div class="relative w-full md:w-80">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary dark:text-blue-400 pointer-events-none transition-colors icon-20">search</span>
          <input v-model="search" type="text" placeholder="Cari Nopol, kendaraan, atau pelanggan..."
            class="search-input-field" />
          <button v-if="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <span class="material-symbols-outlined icon-18">close</span>
          </button>
        </div>
      </div>

      <select v-model="filterStatus" @change="applyFilter" class="form-input py-2 text-sm w-44">
        <option value="">Semua Status</option>
        <option value="tepat waktu">Tepat Waktu</option>
        <option value="terlambat">Terlambat</option>
      </select>

      <div v-if="search" class="flex justify-end w-full md:w-auto">
        <button @click="search = ''" class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 flex items-center gap-1">
          <span class="material-symbols-outlined icon-16">restart_alt</span>
          Reset
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-panel">
      <div class="p-5 border-b border-slate-200 dark:border-slate-700/80 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/60">
        <h3 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white">Daftar Pengembalian</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700/80 text-label-md font-label-md text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <th class="p-4 font-semibold">Kendaraan & Nopol</th>
              <th class="p-4 font-semibold">Pelanggan</th>
              <th class="p-4 font-semibold">Jadwal Kembali</th>
              <th class="p-4 font-semibold">Status</th>
              <th class="p-4 font-semibold">Denda</th>
              <th class="p-4 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-body-md divide-y divide-slate-100 dark:divide-slate-700/50">
            <tr v-if="loading">
              <td colspan="6" class="text-center py-10 text-slate-500 dark:text-slate-400">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
                <p>Memuat data pengembalian...</p>
              </td>
            </tr>
            <tr v-else-if="filteredData.length === 0">
              <td colspan="6" class="text-center py-10 text-slate-500 dark:text-slate-400">
                <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">assignment_return</span>
                Tidak ada data pengembalian ditemukan.
              </td>
            </tr>
            <tr v-for="item in filteredData" :key="item.id"
              class="hover:bg-slate-50/80 dark:hover:bg-slate-750/50 transition-colors"
              :class="item.status === 'terlambat' ? 'bg-rose-50/40 dark:bg-rose-950/20' : ''">
              <td class="p-4">
                <div class="font-semibold text-slate-900 dark:text-white">{{ item.transaksi?.kendaraan?.nama || '-' }}</div>
                <div class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">{{ item.transaksi?.kendaraan?.plat || '-' }}</div>
              </td>
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/60 text-secondary dark:text-blue-300
                              flex items-center justify-center font-bold text-label-sm shrink-0">
                    {{ initials(item.transaksi?.pelanggan?.nama || '') }}
                  </div>
                  <div>
                    <div class="text-slate-900 dark:text-slate-100 font-medium">{{ item.transaksi?.pelanggan?.nama || '-' }}</div>
                    <div class="text-xs text-slate-400 dark:text-slate-500">{{ item.transaksi?.pelanggan?.telepon || '-' }}</div>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <span :class="item.status === 'terlambat' ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-700 dark:text-slate-300'">
                  {{ item.tanggal_kembali }}
                </span>
              </td>
              <td class="p-4">
                <StatusBadge :status="item.status" />
              </td>
              <td class="p-4 font-semibold" :class="item.denda ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500'">
                <div>{{ item.denda ? formatRupiah(item.denda) : '-' }}</div>
                <StatusBadge v-if="item.denda_status" :status="item.denda_status" class="mt-1" />
              </td>
              <td class="p-4 text-right">
                <button v-if="item.denda_status === 'belum bayar'" @click="lunasi(item)"
                  class="bg-secondary text-on-secondary px-4 py-2 rounded-lg text-label-md font-bold hover:bg-secondary-container transition-colors shadow-sm">
                  Lunasi Denda (Tunai)
                </button>
                <span v-else class="text-label-md text-slate-400 dark:text-slate-500">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-4 border-t border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-900/60 flex flex-col sm:flex-row justify-between items-center gap-3" v-if="pagination">
        <p class="text-label-sm font-label-sm text-slate-500 dark:text-slate-400">
          Menampilkan {{ (currentPage - 1) * pagination.per_page + 1 }}-{{ Math.min(currentPage * pagination.per_page, pagination.total) || 0 }} dari {{ pagination.total }} data
        </p>
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

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToastStore } from '@/stores/toast'
import api from '@/services/api'
import StatusBadge from '@/components/ui/StatusBadge.vue'

const toast     = useToastStore()
const search    = ref('')
const filterStatus = ref('')
const loading   = ref(false)
const currentPage = ref(1)
const pagination = ref(null)
const data      = ref([])

const fetchPengembalian = async () => {
  loading.value = true
  try {
    const response = await api.get('/pengembalian', {
      params: { page: currentPage.value, status: filterStatus.value }
    })
    data.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    toast.error('Gagal', 'Gagal memuat data pengembalian')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPengembalian()
})

const changePage = (page) => {
  currentPage.value = page
  fetchPengembalian()
}

const terlambatCount = computed(() => data.value.filter(d => d.status === 'terlambat').length)
const hariIniCount   = computed(() => data.value.filter(d => d.status === 'tepat waktu' || d.status === 'hari ini').length)

const filteredData   = computed(() => {
  if (!search.value) return data.value
  const q = search.value.toLowerCase()
  return data.value.filter(d => 
    d.transaksi?.kendaraan?.nama?.toLowerCase().includes(q) || 
    d.transaksi?.pelanggan?.nama?.toLowerCase().includes(q) || 
    d.transaksi?.kendaraan?.plat?.toLowerCase().includes(q)
  )
})

function applyFilter() {
  currentPage.value = 1
  fetchPengembalian()
}

function initials(n) { 
  if (!n) return ''
  return n.split(' ').map(x=>x[0]).slice(0,2).join('').toUpperCase() 
}
function formatRupiah(n){ return 'Rp ' + Number(n).toLocaleString('id-ID') }

async function lunasi(item) {
  try {
    await api.patch(`/pengembalian/${item.id}/lunas`)
    toast.success('Lunas', 'Denda dicatat lunas (tunai).')
    fetchPengembalian()
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Gagal melunasi denda')
  }
}
</script>
