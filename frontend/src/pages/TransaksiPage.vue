<template>
  <div>
    <h1 class="text-headline-lg font-headline-lg text-primary mb-8">Manajemen Transaksi</h1>

    <!-- Filters -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6 mb-6 flex flex-col md:flex-row justify-between items-end gap-4 border border-outline-variant/30">
      <div class="flex flex-wrap gap-4 w-full md:w-auto">
        <div class="flex flex-col gap-1">
          <label class="text-label-sm font-label-sm text-on-surface-variant">Tanggal Mulai</label>
          <input type="date" v-model="filterMulai"
            class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:border-secondary outline-none" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-label-sm font-label-sm text-on-surface-variant">Tanggal Akhir</label>
          <input type="date" v-model="filterAkhir"
            class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:border-secondary outline-none" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-label-sm font-label-sm text-on-surface-variant">Status</label>
          <select v-model="filterStatus"
            class="px-3 py-2 border border-outline-variant rounded-lg text-body-md focus:border-secondary outline-none bg-white">
            <option value="">Semua Status</option>
            <option value="selesai">Selesai</option>
            <option value="menunggu">Menunggu</option>
            <option value="aktif">Aktif</option>
            <option value="dibatalkan">Dibatalkan</option>
          </select>
        </div>
      </div>
      <button class="bg-secondary text-on-secondary px-6 py-2 rounded-lg font-bold text-label-md
                     flex items-center gap-2 h-10 shadow-sm hover:bg-secondary-container transition-colors">
        <span class="material-symbols-outlined" style="font-size:18px">download</span>
        Export Excel
      </button>
    </div>

    <!-- Table -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low/50 border-b border-outline-variant text-on-surface-variant text-label-sm font-label-sm uppercase tracking-wider">
            <th class="py-4 px-6 font-semibold">ID Transaksi</th>
            <th class="py-4 px-6 font-semibold">Tanggal</th>
            <th class="py-4 px-6 font-semibold">Pelanggan</th>
            <th class="py-4 px-6 font-semibold">Kendaraan</th>
            <th class="py-4 px-6 font-semibold">Total</th>
            <th class="py-4 px-6 font-semibold text-center">Status</th>
            <th class="py-4 px-6 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-body-md font-body-md divide-y divide-outline-variant/20">
          <tr v-if="loading">
            <td colspan="7" class="py-8 text-center text-on-surface-variant">Memuat data transaksi...</td>
          </tr>
          <tr v-else-if="transaksi.length === 0">
            <td colspan="7" class="py-8 text-center text-on-surface-variant">Belum ada data transaksi.</td>
          </tr>
          <tr v-else v-for="t in transaksi" :key="t.id" class="hover:bg-surface-container-lowest transition-colors">
            <td class="py-4 px-6 font-semibold text-primary">{{ t.id }}</td>
            <td class="py-4 px-6 text-on-surface-variant">{{ formatDate(t.tanggal_mulai) }} - {{ formatDate(t.tanggal_akhir) }}</td>
            <td class="py-4 px-6">{{ t.pelanggan?.nama }}</td>
            <td class="py-4 px-6">{{ t.kendaraan?.nama }}</td>
            <td class="py-4 px-6">{{ formatRupiah(t.total) }}</td>
            <td class="py-4 px-6 text-center">
              <StatusBadge :status="t.status" />
            </td>
            <td class="py-4 px-6 text-right">
              <button @click="openDetail(t)"
                class="text-secondary hover:text-secondary-container p-1 transition-colors">
                <span class="material-symbols-outlined" style="font-size:20px">visibility</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination -->
      <div class="flex items-center justify-between px-6 py-4 border-t border-outline-variant/30">
        <span class="text-label-sm font-label-sm text-on-surface-variant">
          Menampilkan {{ pagination?.from || 0 }}-{{ pagination?.to || 0 }} dari {{ pagination?.total || 0 }}
        </span>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1"
            class="w-8 h-8 rounded bg-surface-container-high text-on-surface hover:bg-surface-container-highest disabled:opacity-50 font-bold text-label-sm flex items-center justify-center">
            &lt;
          </button>
          <button class="w-8 h-8 rounded bg-secondary text-on-secondary font-bold text-label-sm flex items-center justify-center">
            {{ currentPage }}
          </button>
          <button @click="currentPage++" :disabled="currentPage === pagination?.last_page || !pagination?.last_page"
            class="w-8 h-8 rounded bg-surface-container-high text-on-surface hover:bg-surface-container-highest disabled:opacity-50 font-bold text-label-sm flex items-center justify-center">
            &gt;
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Detail Transaksi -->
    <BaseModal v-model="showDetail" max-width="720px">
      <template #header>
        <div class="flex items-center gap-4">
          <h2 class="text-headline-md font-headline-md text-on-surface">Detail Transaksi</h2>
          <StatusBadge v-if="selected" :status="selected.status" />
          <span class="text-label-md font-label-md text-on-surface-variant">{{ selected?.id }}</span>
        </div>
      </template>

      <div v-if="selected" class="flex flex-col gap-6">
        <!-- 2-col info cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-[#F4F5F7] rounded-lg p-5 border border-outline-variant/20 flex gap-4 items-start">
            <div class="w-20 h-20 rounded-md bg-surface-container-high flex items-center justify-center shrink-0">
              <span class="material-symbols-outlined text-outline text-4xl">directions_car</span>
            </div>
            <div>
              <p class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Informasi Kendaraan</p>
              <p class="text-body-lg font-body-lg font-semibold text-on-surface">{{ selected.kendaraan?.nama }}</p>
              <p class="text-label-md font-label-md text-on-surface-variant">{{ selected.kendaraan?.tipe }} • {{ selected.kendaraan?.kapasitas }} Penumpang</p>
            </div>
          </div>
          <div class="bg-[#F4F5F7] rounded-lg p-5 border border-outline-variant/20 flex gap-4 items-start">
            <div class="w-16 h-16 rounded-full bg-secondary-fixed-dim text-secondary
                        flex items-center justify-center font-bold text-xl shrink-0">
              {{ initials(selected.pelanggan?.nama) }}
            </div>
            <div>
              <p class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Informasi Pelanggan</p>
              <p class="text-body-lg font-body-lg font-semibold text-on-surface">{{ selected.pelanggan?.nama }}</p>
              <p class="text-label-md font-label-md text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:14px">mail</span>
                {{ selected.pelanggan?.email }}
              </p>
            </div>
          </div>
        </div>

        <!-- Detail pemesanan -->
        <div class="bg-surface border border-outline-variant/30 rounded-lg p-5">
          <h3 class="text-body-md font-semibold text-on-surface mb-4">Detail Pemesanan</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
              <p class="text-label-sm font-label-sm text-on-surface-variant mb-1">Tanggal Sewa</p>
              <p class="text-label-md font-medium text-on-surface">{{ formatDate(selected.tanggal_mulai) }}</p>
            </div>
            <div>
              <p class="text-label-sm font-label-sm text-on-surface-variant mb-1">Tanggal Kembali</p>
              <p class="text-label-md font-medium text-on-surface">{{ formatDate(selected.tanggal_akhir) }}</p>
            </div>
            <div>
              <p class="text-label-sm font-label-sm text-on-surface-variant mb-1">Durasi</p>
              <p class="text-label-md font-medium text-on-surface">{{ getDurasi(selected.tanggal_mulai, selected.tanggal_akhir) }}</p>
            </div>
            <div>
              <p class="text-label-sm font-label-sm text-on-surface-variant mb-1">Total Biaya</p>
              <p class="text-label-md font-bold text-secondary">{{ formatRupiah(selected.total) }}</p>
            </div>
          </div>
        </div>

        <!-- Stepper status -->
        <div>
          <h3 class="text-body-md font-semibold text-on-surface mb-6">Status Pemesanan</h3>
          <div class="relative flex items-center justify-between w-full pb-4">
            <div class="absolute top-4 left-4 right-4 h-0.5 bg-outline-variant/40 -z-10"></div>
            <div class="absolute top-4 left-4 h-0.5 bg-secondary -z-10"
              :style="{ width: stepProgress }"></div>
            <div v-for="(step, i) in steps" :key="step"
              class="flex flex-col items-center gap-2 relative z-10">
              <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-sm"
                :class="i < currentStep
                  ? 'bg-secondary text-on-secondary'
                  : i === currentStep
                    ? 'border-2 border-secondary bg-surface'
                    : 'bg-surface border-2 border-outline-variant/40 text-on-surface-variant'">
                <span v-if="i < currentStep" class="material-symbols-outlined" style="font-size:18px">check</span>
                <div v-else-if="i === currentStep" class="w-3 h-3 rounded-full bg-secondary"></div>
                <span v-else class="material-symbols-outlined" style="font-size:16px">more_horiz</span>
              </div>
              <span class="text-label-sm font-label-sm text-center"
                :class="i === currentStep ? 'text-secondary font-bold' : i < currentStep ? 'text-on-surface font-medium' : 'text-on-surface-variant'">
                {{ step }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <button @click="showDetail = false"
          class="px-5 py-2.5 rounded border border-primary text-primary hover:bg-surface-container-low
                 text-label-md font-label-md font-semibold transition-colors">
          Tutup
        </button>
        <button v-if="selected?.status === 'aktif'" @click="tandaiKembali"
          class="px-5 py-2.5 rounded bg-emerald-500 text-white hover:bg-emerald-600
                 text-label-md font-label-md font-bold shadow-sm flex items-center gap-2 transition-colors">
          <span class="material-symbols-outlined" style="font-size:18px">task_alt</span>
          Tandai Dikembalikan
        </button>
        <template v-if="selected?.status === 'menunggu'">
          <button @click="tolakTransaksi"
            class="px-5 py-2.5 rounded border border-error text-error hover:bg-error-container/20
                   text-label-md font-label-md font-semibold transition-colors">
            Tolak
          </button>
          <button @click="konfirmasiTransaksi"
            class="px-5 py-2.5 rounded bg-secondary text-on-secondary hover:bg-secondary-container
                   text-label-md font-label-md font-bold shadow-sm transition-colors">
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
import api from '@/services/api'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import BaseModal   from '@/components/ui/BaseModal.vue'

const toast = useToastStore()

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

watch([filterMulai, filterAkhir, filterStatus], () => {
  currentPage.value = 1
  fetchTransaksi()
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
</script>
