<template>
  <div>
    <div class="mb-6">
      <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Katalog Kendaraan</h1>
      <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Pilih kendaraan, cek ketersediaan, lalu booking.</p>
    </div>

    <!-- Filters -->
    <div class="filter-panel mb-6 flex flex-col lg:flex-row gap-3 lg:items-center">
      <div class="relative w-full lg:w-72">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary dark:text-blue-400 pointer-events-none icon-20">search</span>
        <input v-model="search" type="text" placeholder="Cari nama kendaraan..." class="search-input-field" />
      </div>
      <select v-model="tipe" class="form-input py-2.5 lg:w-44">
        <option value="">Semua Tipe</option>
        <option v-for="t in tipeList" :key="t" :value="t">{{ t }}</option>
      </select>
      <div class="flex items-center gap-2">
        <input type="date" v-model="tanggalMulai" :min="today" title="Tanggal Mulai" class="form-input py-2 text-sm" />
        <span class="text-slate-400 dark:text-slate-500">-</span>
        <input type="date" v-model="tanggalAkhir" :min="tanggalMulai || today" title="Tanggal Akhir" class="form-input py-2 text-sm" />
      </div>
      <button v-if="search || tipe || tanggalMulai || tanggalAkhir" @click="resetFilters"
        class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 flex items-center gap-1">
        <span class="material-symbols-outlined icon-16">restart_alt</span>
        Reset
      </button>
    </div>

    <!-- Grid -->
    <div v-if="loading" class="py-16 text-center text-slate-500 dark:text-slate-400">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
      <p>Memuat katalog...</p>
    </div>
    <div v-else-if="items.length === 0" class="py-16 text-center text-slate-500 dark:text-slate-400">
      <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">search_off</span>
      Tidak ada kendaraan ditemukan.
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <RouterLink v-for="k in items" :key="k.id" :to="{ path: `/kendaraan/${k.id}`, query: detailQuery }"
        class="glass-card rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col">
        <div class="aspect-[4/3] bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center overflow-hidden">
          <img v-if="k.foto_url" :src="k.foto_url" :alt="k.nama" class="w-full h-full object-cover" />
          <span v-else class="material-symbols-outlined text-slate-400 dark:text-slate-500 text-5xl">directions_car</span>
        </div>
        <div class="p-4 flex flex-col gap-1 flex-1">
          <span class="font-semibold text-slate-900 dark:text-white truncate">{{ k.nama }}</span>
          <span class="text-xs text-slate-500 dark:text-slate-400">{{ k.tipe }} · {{ k.tahun }} · {{ k.kapasitas }} kursi</span>
          <span class="mt-auto pt-2 font-bold text-secondary dark:text-blue-400">
            {{ formatRupiah(k.harga) }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">/ hari</span>
          </span>
        </div>
      </RouterLink>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2 mt-8">
      <button @click="page--" :disabled="page === 1"
        class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-secondary dark:text-blue-400 font-bold text-label-md hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
        Prev
      </button>
      <span class="px-3 py-1.5 rounded-lg bg-secondary text-on-secondary font-bold text-label-md">{{ page }} / {{ pagination.last_page }}</span>
      <button @click="page++" :disabled="page >= pagination.last_page"
        class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-secondary dark:text-blue-400 font-bold text-label-md hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'

const toast = useToastStore()

const tipeList = ['SUV', 'Sedan', 'MPV', 'Hatchback', 'Minibus']
const today = new Date().toLocaleDateString('sv-SE') // YYYY-MM-DD lokal, bukan UTC

const search       = ref('')
const tipe         = ref('')
const tanggalMulai = ref('')
const tanggalAkhir = ref('')
const page         = ref(1)
const items        = ref([])
const pagination   = ref({ last_page: 1 })
const loading      = ref(false)

// Teruskan tanggal terpilih ke halaman detail
const detailQuery = computed(() =>
  tanggalMulai.value && tanggalAkhir.value
    ? { mulai: tanggalMulai.value, akhir: tanggalAkhir.value }
    : {}
)

function formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID') }

async function fetchKatalog() {
  loading.value = true
  try {
    const res = await api.get('/katalog', {
      params: {
        search: search.value || undefined,
        tipe: tipe.value || undefined,
        tanggal_mulai: tanggalMulai.value || undefined,
        tanggal_akhir: tanggalAkhir.value || undefined,
        page: page.value,
      },
    })
    items.value = res.data.data
    pagination.value = res.data
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Gagal memuat katalog.')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  search.value = ''
  tipe.value = ''
  tanggalMulai.value = ''
  tanggalAkhir.value = ''
}

let debounceTimer
watch([search, tipe, tanggalMulai, tanggalAkhir], () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    if (page.value !== 1) page.value = 1 // watcher page memicu fetch
    else fetchKatalog()
  }, 300)
})
watch(page, fetchKatalog)

onMounted(fetchKatalog)
</script>
