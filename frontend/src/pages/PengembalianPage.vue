<template>
  <div>
    <div class="flex justify-between items-end mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg text-on-surface mb-2">Manajemen Pengembalian</h1>
        <p class="text-body-md font-body-md text-on-surface-variant">Kelola kendaraan yang harus dikembalikan.</p>
      </div>
      <!-- Quick stats -->
      <div class="flex gap-4">
        <div class="glass-card p-4 rounded-xl flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-error/10 flex items-center justify-center text-error">
            <span class="material-symbols-outlined">warning</span>
          </div>
          <div>
            <p class="text-label-sm font-label-sm text-on-surface-variant">Terlambat</p>
            <p class="text-headline-md font-headline-md text-on-surface">{{ terlambatCount }}</p>
          </div>
        </div>
        <div class="glass-card p-4 rounded-xl flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-secondary/10 flex items-center justify-center text-secondary">
            <span class="material-symbols-outlined">today</span>
          </div>
          <div>
            <p class="text-label-sm font-label-sm text-on-surface-variant">Hari Ini</p>
            <p class="text-headline-md font-headline-md text-on-surface">{{ hariIniCount }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface rounded-xl shadow-sm border border-outline-variant p-4 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="flex flex-wrap gap-4 w-full md:w-auto">
        <div class="relative w-full md:w-64">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size:18px">search</span>
          <input v-model="search" type="text" placeholder="Cari Nopol atau Pelanggan..."
            class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg
                   text-body-md font-body-md focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary/50 transition-all" />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0px_4px_20px_rgba(15,23,42,0.05)] border border-surface-variant overflow-hidden relative">
      <div class="p-6 border-b border-surface-variant flex justify-between items-center bg-surface-bright">
        <h3 class="text-headline-md font-headline-md text-on-surface">Daftar Kendaraan</h3>
      </div>
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low border-b border-surface-variant text-label-md font-label-md text-on-surface-variant uppercase tracking-wider">
            <th class="p-4 font-semibold">Kendaraan & Nopol</th>
            <th class="p-4 font-semibold">Pelanggan</th>
            <th class="p-4 font-semibold">Jadwal Kembali</th>
            <th class="p-4 font-semibold">Status</th>
            <th class="p-4 font-semibold">Denda (Est)</th>
            <th class="p-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-body-md divide-y divide-surface-variant">
          <tr v-if="loading">
            <td colspan="6" class="text-center py-8 text-on-surface-variant">Memuat data...</td>
          </tr>
          <tr v-else-if="filteredData.length === 0">
            <td colspan="6" class="text-center py-8 text-on-surface-variant">Tidak ada data pengembalian.</td>
          </tr>
          <tr v-for="item in filteredData" :key="item.id"
            class="hover:bg-surface-container-low/50 transition-colors"
            :class="item.status === 'terlambat' ? 'bg-error/5' : ''">
            <td class="p-4">
              <div class="font-semibold text-on-surface">{{ item.transaksi?.kendaraan?.nama || '-' }}</div>
              <div class="text-label-sm font-label-sm text-on-surface-variant mt-1">{{ item.transaksi?.kendaraan?.plat || '-' }}</div>
            </td>
            <td class="p-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-primary-fixed-dim text-on-primary-fixed
                            flex items-center justify-center font-bold text-label-sm">
                  {{ initials(item.transaksi?.pelanggan?.nama || '') }}
                </div>
                <div>
                  <div class="text-on-surface font-medium">{{ item.transaksi?.pelanggan?.nama || '-' }}</div>
                  <div class="text-label-sm font-label-sm text-on-surface-variant">{{ item.transaksi?.pelanggan?.telepon || '-' }}</div>
                </div>
              </div>
            </td>
            <td class="p-4">
              <span :class="item.status === 'terlambat' ? 'text-error font-medium' : 'text-on-surface'">
                {{ item.tanggal_kembali }}
              </span>
            </td>
            <td class="p-4">
              <StatusBadge :status="item.status" />
            </td>
            <td class="p-4 font-semibold" :class="item.denda ? 'text-error' : 'text-on-surface-variant'">
              {{ item.denda ? formatRupiah(item.denda) : '-' }}
            </td>
            <td class="p-4 text-right">
              <button v-if="item.status !== 'selesai'" @click="openTerima(item)"
                class="bg-secondary text-on-secondary px-4 py-2 rounded-lg text-label-md font-label-md
                       font-bold hover:bg-secondary/90 transition-colors">
                Terima
              </button>
              <span v-else class="text-label-md text-on-surface-variant font-medium">Selesai</span>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="p-4 border-t border-surface-variant bg-surface-bright flex justify-between items-center" v-if="pagination">
        <p class="text-label-sm font-label-sm text-on-surface-variant">
          Menampilkan {{ (currentPage - 1) * pagination.per_page + 1 }}-{{ Math.min(currentPage * pagination.per_page, pagination.total) || 0 }} dari {{ pagination.total }} data
        </p>
        <div class="flex gap-1">
          <button v-for="page in pagination.last_page" :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-1 rounded-md font-medium transition-colors text-label-sm',
              page === currentPage ? 'bg-secondary text-on-secondary' : 'bg-surface text-on-surface hover:bg-surface-container-low border border-outline-variant'
            ]">
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Detail Pengembalian -->
    <BaseModal v-model="showModal" max-width="600px">
      <template #header>
        <h2 class="text-headline-md font-headline-md text-primary">Detail Pengembalian</h2>
      </template>

      <div v-if="selected" class="flex flex-col gap-6">
        <!-- Info grid -->
        <div class="grid grid-cols-2 gap-y-4 gap-x-6">
          <div v-for="row in infoRows" :key="row.label" class="flex flex-col"
            :class="row.full ? 'col-span-2' : ''">
            <span class="text-label-sm font-label-sm text-on-surface-variant mb-1 uppercase tracking-wider">
              {{ row.label }}
            </span>
            <span class="text-body-md font-body-md text-on-surface" :class="row.bold ? 'font-semibold' : ''">
              {{ row.value }}
            </span>
          </div>
        </div>

        <!-- Denda warning -->
        <div v-if="selected.denda"
          class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
          <span class="material-symbols-outlined text-amber-500 fill mt-0.5">warning</span>
          <div>
            <h3 class="text-label-md font-label-md text-amber-900 font-bold mb-1">
              Keterlambatan · Denda: {{ formatRupiah(selected.denda) }}
            </h3>
            <p class="text-sm text-amber-800">Denda dihitung berdasarkan tarif harian.</p>
          </div>
        </div>

        <!-- Catatan kondisi -->
        <div class="flex flex-col gap-2">
          <label class="text-label-md font-label-md text-primary">Catatan Kondisi Kendaraan</label>
          <textarea v-model="catatan" rows="3" placeholder="Misal: Baret halus di bumper depan, bensin full."
            class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2
                   text-body-md focus:border-secondary focus:outline-none focus:ring-1
                   focus:ring-secondary transition-all resize-none"></textarea>
        </div>
      </div>

      <template #footer>
        <button @click="showModal = false"
          class="px-6 py-2 rounded-lg border border-primary text-primary bg-white
                 hover:bg-surface-variant text-label-md font-label-md transition-colors">
          Batal
        </button>
        <button @click="selesaikan"
          class="px-6 py-2 rounded-lg bg-secondary text-on-secondary font-bold
                 hover:bg-secondary-container text-label-md shadow-sm transition-colors">
          Simpan & Selesaikan
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

const toast     = useToastStore()
const search    = ref('')
const showModal = ref(false)
const selected  = ref(null)
const catatan   = ref('')
const loading   = ref(false)
const currentPage = ref(1)
const pagination = ref(null)
const data      = ref([])

const fetchPengembalian = async () => {
  loading.value = true
  try {
    const response = await api.get(`/pengembalian?page=${currentPage.value}&status=`)
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

const infoRows = computed(() => selected.value ? [
  { label:'Pelanggan',               value: selected.value.transaksi?.pelanggan?.nama || '-' },
  { label:'Kendaraan',               value: `${selected.value.transaksi?.kendaraan?.nama || ''} - ${selected.value.transaksi?.kendaraan?.plat || ''}` },
  { label:'Jadwal Kembali',          value: selected.value.tanggal_kembali },
  { label:'Status',                  value: selected.value.status },
  { label:'Denda Estimasi', full:true, bold:true, value: selected.value.denda ? formatRupiah(selected.value.denda) : 'Tidak ada denda' },
] : [])

function initials(n) { 
  if (!n) return ''
  return n.split(' ').map(x=>x[0]).slice(0,2).join('').toUpperCase() 
}
function formatRupiah(n){ return 'Rp ' + Number(n).toLocaleString('id-ID') }

function openTerima(item) {
  selected.value = item
  catatan.value  = ''
  showModal.value = true
}

async function selesaikan() {
  try {
    await api.patch(`/pengembalian/${selected.value.id}/terima`, {
      kondisi_kendaraan: catatan.value
    })
    toast.success('Selesai', `Pengembalian ${selected.value.transaksi?.kendaraan?.nama || 'kendaraan'} berhasil dicatat.`)
    showModal.value = false
    fetchPengembalian()
  } catch (error) {
    toast.error('Gagal', 'Gagal memproses pengembalian')
  }
}
</script>
