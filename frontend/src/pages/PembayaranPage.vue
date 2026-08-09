<template>
  <div>
    <div class="flex justify-between items-end mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg text-primary mb-2">Manajemen Pembayaran</h1>
        <p class="text-body-md font-body-md text-on-surface-variant">Verifikasi dan kelola transaksi pembayaran pelanggan.</p>
      </div>
      <div class="flex gap-4">
        <button class="bg-surface border border-outline-variant px-4 py-2 rounded-lg text-label-md font-label-md
                       hover:bg-surface-container-low flex items-center gap-2 text-secondary transition-colors">
          <span class="material-symbols-outlined" style="font-size:18px">filter_list</span>
          Filter
        </button>
        <button class="bg-secondary text-on-secondary px-4 py-2 rounded-lg text-label-md font-label-md font-bold
                       hover:bg-secondary-container flex items-center gap-2 shadow-sm transition-colors">
          <span class="material-symbols-outlined" style="font-size:18px">download</span>
          Export Laporan
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div v-for="stat in stats" :key="stat.label"
        class="bg-white/80 backdrop-blur-md border border-white/20 rounded-xl p-6
               shadow-[0px_4px_20px_rgba(15,23,42,0.05)] relative overflow-hidden">
        <div class="flex justify-between items-start mb-4">
          <p class="text-label-md font-label-md text-on-surface-variant">{{ stat.label }}</p>
          <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="stat.iconBg">
            <span class="material-symbols-outlined" :class="stat.iconColor">{{ stat.icon }}</span>
          </div>
        </div>
        <h3 class="text-headline-lg font-headline-lg text-primary">{{ stat.value }}</h3>
        <p class="text-label-sm font-label-sm mt-2 flex items-center gap-1" :class="stat.trendColor">
          <span class="material-symbols-outlined" style="font-size:14px">{{ stat.trendIcon }}</span>
          {{ stat.trend }}
        </p>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-[0px_4px_20px_rgba(15,23,42,0.05)] overflow-hidden border border-outline-variant/30">
      <div class="p-6 border-b border-surface-container-high flex justify-between items-center bg-surface-bright">
        <h2 class="text-headline-md font-headline-md text-primary">Daftar Transaksi</h2>
        <div class="relative w-64">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant"
            style="font-size:18px">search</span>
          <input v-model="search" type="text" placeholder="Cari ID Transaksi..."
            class="w-full pl-10 pr-4 py-2 bg-surface rounded-lg border border-outline-variant
                   text-body-md focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary/50 transition-all" />
        </div>
      </div>
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low text-on-surface-variant text-label-md font-label-md border-b border-surface-container-high">
            <th class="py-4 px-6 font-semibold">ID Transaksi</th>
            <th class="py-4 px-6 font-semibold">Pelanggan</th>
            <th class="py-4 px-6 font-semibold">Metode</th>
            <th class="py-4 px-6 font-semibold">Jumlah</th>
            <th class="py-4 px-6 font-semibold">Status</th>
            <th class="py-4 px-6 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-body-md divide-y divide-surface-container-high">
          <tr v-for="p in pembayaran" :key="p.id"
            class="hover:bg-surface-container-lowest transition-colors group">
            <td class="py-4 px-6 font-semibold text-primary">{{ p.id }}</td>
            <td class="py-4 px-6">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-secondary-fixed text-on-secondary-fixed
                            flex items-center justify-center font-bold text-label-sm">
                  {{ initials(p.pelanggan) }}
                </div>
                <div>
                  <p class="font-medium text-on-surface">{{ p.pelanggan }}</p>
                  <p class="text-label-sm text-on-surface-variant">{{ p.email }}</p>
                </div>
              </div>
            </td>
            <td class="py-4 px-6">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size:20px">{{ metodeIcon(p.metode) }}</span>
                <span>{{ p.metode }}</span>
              </div>
            </td>
            <td class="py-4 px-6 font-medium">{{ formatRupiah(p.jumlah) }}</td>
            <td class="py-4 px-6">
              <StatusBadge :status="p.status" />
            </td>
            <td class="py-4 px-6 text-right">
              <!-- Menunggu: approve/reject -->
              <div v-if="p.status === 'menunggu verifikasi'"
                class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="tolak(p)"
                  class="p-1.5 text-error hover:bg-error-container/50 rounded-md transition-colors">
                  <span class="material-symbols-outlined" style="font-size:20px">close</span>
                </button>
                <button @click="setujui(p)"
                  class="p-1.5 text-secondary hover:bg-primary-fixed/50 rounded-md transition-colors">
                  <span class="material-symbols-outlined" style="font-size:20px">check</span>
                </button>
              </div>
              <!-- Berhasil: detail -->
              <button v-else @click="openDetail(p)"
                class="text-on-surface-variant hover:text-secondary text-label-md font-medium transition-colors">
                Detail
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination -->
      <div class="p-4 border-t border-surface-container-high flex items-center justify-between bg-surface-bright">
        <span class="text-label-md font-label-md text-on-surface-variant">Menampilkan 1-{{ pembayaran.length }}</span>
        <div class="flex gap-1">
          <button class="px-3 py-1 bg-secondary text-on-secondary rounded-md font-medium">1</button>
        </div>
      </div>
    </div>

    <!-- Modal Detail Pembayaran -->
    <BaseModal v-model="showDetail" max-width="560px">
      <template #header>
        <div class="flex items-center gap-3">
          <h2 class="text-headline-md font-headline-md text-primary">Detail Pembayaran</h2>
          <StatusBadge v-if="selected" :status="selected.status" />
        </div>
      </template>

      <div v-if="selected" class="flex flex-col gap-4">
        <div v-for="row in detailRows" :key="row.label"
          class="flex justify-between items-center py-2 border-b border-surface-container-high/50 last:border-0">
          <span class="text-label-md font-label-md text-on-surface-variant">{{ row.label }}</span>
          <span class="text-body-md font-semibold text-primary">{{ row.value }}</span>
        </div>
      </div>

      <template #footer>
        <button @click="showDetail = false"
          class="px-5 py-2.5 rounded-lg border border-primary text-primary text-label-md font-semibold
                 hover:bg-surface-container transition-colors">
          Tutup
        </button>
        <button v-if="selected?.status === 'menunggu verifikasi'" @click="konfirmasi"
          class="w-full bg-secondary hover:bg-secondary-container text-on-secondary font-bold py-3 px-4
                 rounded-lg transition-colors flex items-center justify-center gap-2">
          <span class="material-symbols-outlined">check_circle</span>
          Konfirmasi Pembayaran
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useToastStore } from '@/stores/toast'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import BaseModal   from '@/components/ui/BaseModal.vue'

const toast      = useToastStore()
const search     = ref('')
const showDetail = ref(false)
const selected   = ref(null)

const stats = [
  { label:'Menunggu Verifikasi', value:'12', icon:'pending_actions', iconBg:'bg-error-container/50',     iconColor:'text-on-error-container',   trend:'+3 dari kemarin',       trendIcon:'arrow_upward', trendColor:'text-error'     },
  { label:'Berhasil (Hari ini)', value:'48', icon:'check_circle',    iconBg:'bg-primary-fixed/50',       iconColor:'text-secondary',            trend:'+15% dari rata-rata',   trendIcon:'arrow_upward', trendColor:'text-secondary'  },
  { label:'Total Pending',       value:'Rp 15.450.000', icon:'payments', iconBg:'bg-tertiary-fixed-dim/30', iconColor:'text-on-tertiary-container', trend:'Menunggu konfirmasi', trendIcon:'info',         trendColor:'text-on-surface-variant' },
]

const pembayaran = ref([
  { id:'TRX-9901-A2', pelanggan:'Budi Darmawan',  email:'budi@email.com',  metode:'Bank Transfer (BCA)',    jumlah:2500000, status:'menunggu verifikasi' },
  { id:'TRX-9902-B3', pelanggan:'Siti Wulandari', email:'siti.w@email.com', metode:'QRIS',                   jumlah:850000,  status:'berhasil'            },
  { id:'TRX-9903-C4', pelanggan:'Agus Setiawan',  email:'agus@email.com',  metode:'Bank Transfer (Mandiri)',jumlah:4200000, status:'menunggu verifikasi' },
])

const detailRows = computed(() => selected.value ? [
  { label: 'ID Transaksi',   value: selected.value.id },
  { label: 'Nama Pelanggan', value: selected.value.pelanggan },
  { label: 'Metode',         value: selected.value.metode },
  { label: 'Jumlah',         value: formatRupiah(selected.value.jumlah) },
  { label: 'Status',         value: selected.value.status },
] : [])

function initials(name) { return name.split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase() }
function formatRupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID') }
function metodeIcon(m) {
  if (m.includes('QRIS')) return 'qr_code_scanner'
  if (m.includes('Bank')) return 'account_balance'
  return 'payments'
}
function openDetail(p) { selected.value = p; showDetail.value = true }
function setujui(p) {
  p.status = 'berhasil'
  toast.success('Berhasil', `Pembayaran ${p.id} dikonfirmasi.`)
}
function tolak(p) {
  p.status = 'dibatalkan'
  toast.error('Ditolak', `Pembayaran ${p.id} ditolak.`)
}
function konfirmasi() {
  setujui(selected.value)
  showDetail.value = false
}
</script>
