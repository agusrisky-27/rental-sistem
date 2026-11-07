<template>
  <div>
    <div class="mb-6">
      <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Booking Saya</h1>
      <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Pantau status sewa dan pembayaran Anda.</p>
    </div>

    <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 p-4 text-sm text-amber-900 dark:text-amber-300">
      <span class="material-symbols-outlined icon-20 mt-0.5">info</span>
      Booking yang belum diunggah bukti pembayarannya akan dibatalkan otomatis setelah 24 jam.
    </div>

    <div v-if="loading" class="py-16 text-center text-slate-500 dark:text-slate-400">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
      <p>Memuat booking...</p>
    </div>
    <div v-else-if="items.length === 0" class="py-16 text-center text-slate-500 dark:text-slate-400">
      <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">receipt_long</span>
      Belum ada booking.
      <RouterLink to="/" class="block mt-2 text-secondary dark:text-blue-400 font-semibold hover:underline">Lihat katalog kendaraan</RouterLink>
    </div>

    <div v-else class="flex flex-col gap-4">
      <div v-for="t in items" :key="t.id" class="glass-card rounded-xl p-4 shadow-sm flex flex-col sm:flex-row gap-4">
        <div class="w-full sm:w-40 h-28 shrink-0 rounded-lg bg-slate-100 dark:bg-slate-700/60 overflow-hidden flex items-center justify-center border border-slate-200/50 dark:border-slate-700">
          <img v-if="t.kendaraan?.foto_url" :src="t.kendaraan.foto_url" :alt="t.kendaraan?.nama" class="w-full h-full object-cover" />
          <span v-else class="material-symbols-outlined text-slate-400 dark:text-slate-500 text-4xl">directions_car</span>
        </div>

        <div class="flex-1 min-w-0 flex flex-col gap-2">
          <div class="flex flex-wrap items-center gap-2">
            <span class="font-semibold text-slate-900 dark:text-white">{{ t.kendaraan?.nama || '-' }}</span>
            <StatusBadge :status="t.status" />
            <span class="text-xs font-mono text-slate-400 dark:text-slate-500">#{{ t.id }}</span>
          </div>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            {{ formatDate(t.tanggal_mulai) }} - {{ formatDate(t.tanggal_akhir) }}
          </p>
          <p class="font-bold text-secondary dark:text-blue-400">{{ formatRupiah(t.total) }}</p>

          <!-- Pembayaran sewa terakhir -->
          <div v-if="t.pembayaran" class="text-sm flex flex-wrap items-center gap-2 text-slate-600 dark:text-slate-400">
            <span>Pembayaran:</span>
            <StatusBadge :status="t.pembayaran.status" />
            <span v-if="t.pembayaran.status === 'ditolak' && t.pembayaran.catatan" class="text-rose-600 dark:text-rose-400">
              Alasan: {{ t.pembayaran.catatan }}
            </span>
          </div>
          <p v-else-if="t.status === 'menunggu'" class="text-sm text-slate-500 dark:text-slate-400">Belum ada bukti pembayaran.</p>

          <!-- Denda -->
          <div v-if="t.pengembalian && t.pengembalian.denda > 0"
            class="text-sm flex flex-wrap items-center gap-2 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60 px-3 py-2 text-rose-800 dark:text-rose-300">
            <span class="material-symbols-outlined icon-18">warning</span>
            <span>Denda keterlambatan {{ formatRupiah(t.pengembalian.denda) }}</span>
            <StatusBadge :status="t.pengembalian.denda_status" />
            <span v-if="dendaRejected(t)" class="text-rose-700 dark:text-rose-300">
              Bukti denda ditolak<span v-if="dendaRejected(t).catatan">: {{ dendaRejected(t).catatan }}</span>
            </span>
          </div>

          <!-- Aksi -->
          <div class="flex flex-wrap gap-2 mt-1">
            <button v-if="bisaUpload(t)" @click="openUpload(t, 'sewa')"
              class="px-4 py-2 rounded-lg bg-secondary text-on-secondary hover:bg-secondary-container text-label-md font-bold shadow-sm transition-colors flex items-center gap-1.5">
              <span class="material-symbols-outlined icon-18">upload</span>
              Upload Bukti Bayar
            </button>
            <button v-if="bisaBayarDenda(t)" @click="openUpload(t, 'denda')"
              class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 text-label-md font-bold shadow-sm transition-colors flex items-center gap-1.5">
              <span class="material-symbols-outlined icon-18">payments</span>
              Bayar Denda
            </button>
            <button v-if="bisaBatal(t)" @click="batal(t)"
              class="px-4 py-2 rounded-lg border border-rose-500 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-label-md font-semibold transition-colors">
              Batalkan
            </button>
          </div>
        </div>
      </div>
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

    <!-- Modal upload bukti -->
    <BaseModal v-model="showUpload" max-width="480px">
      <template #header>
        <h2 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white">
          {{ uploadJenis === 'denda' ? 'Bayar Denda' : 'Upload Bukti Bayar' }}
        </h2>
      </template>

      <div class="flex flex-col gap-4">
        <div v-if="rekeningInfo" class="rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700 p-4">
          <p class="text-label-sm text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Transfer ke</p>
          <p class="text-body-md text-slate-800 dark:text-slate-200 whitespace-pre-line">{{ rekeningInfo }}</p>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-400">
          Jumlah yang harus dibayar:
          <span class="font-bold text-slate-900 dark:text-white">{{ formatRupiah(uploadJumlah) }}</span>
        </p>

        <FormField label="Metode Pembayaran" :error="fieldError('metode')">
          <select v-model="formUpload.metode" class="form-input">
            <option v-for="m in metodeList" :key="m" :value="m">{{ m }}</option>
          </select>
        </FormField>

        <FormField label="Bukti Pembayaran (JPG/PNG, maks 5MB)" :error="fieldError('bukti')">
          <input type="file" accept="image/jpeg,image/png" @change="onFile" class="form-input" />
        </FormField>

        <p v-if="uploadError" class="text-sm font-medium text-rose-500">{{ uploadError }}</p>
      </div>

      <template #footer>
        <button @click="showUpload = false"
          class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-label-md font-semibold transition-colors">
          Batal
        </button>
        <button @click="kirimBukti" :disabled="uploading"
          class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary hover:bg-secondary-container text-label-md font-bold shadow-sm transition-colors disabled:opacity-60">
          {{ uploading ? 'Mengirim...' : 'Kirim' }}
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import FormField from '@/components/ui/FormField.vue'

const toast = useToastStore()

const metodeList = ['Transfer Bank', 'QRIS', 'E-Wallet']

const items      = ref([])
const loading    = ref(false)
const page       = ref(1)
const pagination = ref({ last_page: 1 })
const rekeningInfo = ref('')

// Modal upload
const showUpload   = ref(false)
const uploading    = ref(false)
const uploadTarget = ref(null)
const uploadJenis  = ref('sewa')
const uploadJumlah = ref(0)
const uploadError  = ref('')
const uploadErrors = ref({})
const formUpload   = ref({ metode: 'Transfer Bank', bukti: null })

const fieldError = (k) => uploadErrors.value[k]?.[0] || ''

function formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID') }
function formatDate(s) {
  if (!s) return '-'
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(s))
}

// Laravel menyerialisasi relasi semuaPembayaran sebagai semua_pembayaran
const semua = (t) => t.semua_pembayaran || t.semuaPembayaran || []
const dendaPayments = (t) => semua(t).filter(p => p.jenis === 'denda')
const dendaRejected = (t) => {
  const last = dendaPayments(t).slice(-1)[0]
  return last?.status === 'ditolak' ? last : null
}

const bisaUpload = (t) => t.status === 'menunggu' && (!t.pembayaran || t.pembayaran.status === 'ditolak')
const bisaBatal = (t) => t.status === 'menunggu' && !['berhasil', 'menunggu verifikasi'].includes(t.pembayaran?.status)
const bisaBayarDenda = (t) =>
  t.pengembalian?.denda_status === 'belum bayar' &&
  !dendaPayments(t).some(p => p.status === 'menunggu verifikasi')

async function fetchBooking() {
  loading.value = true
  try {
    const res = await api.get('/akun/transaksi', { params: { page: page.value } })
    items.value = res.data.data
    pagination.value = res.data
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Gagal memuat booking.')
  } finally {
    loading.value = false
  }
}

async function fetchRekening() {
  try {
    const res = await api.get('/settings/public')
    rekeningInfo.value = res.data.data?.rekening_info || ''
  } catch (e) {
    // info rekening opsional
  }
}

function openUpload(t, jenis) {
  uploadTarget.value = t
  uploadJenis.value  = jenis
  uploadJumlah.value = jenis === 'denda' ? t.pengembalian?.denda : t.total
  uploadError.value  = ''
  uploadErrors.value = {}
  formUpload.value   = { metode: 'Transfer Bank', bukti: null }
  showUpload.value   = true
}

function onFile(e) {
  formUpload.value.bukti = e.target.files[0] || null
}

async function kirimBukti() {
  const file = formUpload.value.bukti
  uploadError.value = ''
  uploadErrors.value = {}
  if (!file) {
    uploadError.value = 'Pilih file bukti pembayaran.'
    return
  }
  if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 5 * 1024 * 1024) {
    uploadError.value = 'Bukti harus berupa JPG/PNG maksimal 5MB.'
    return
  }
  const fd = new FormData()
  fd.append('jenis', uploadJenis.value)
  fd.append('metode', formUpload.value.metode)
  fd.append('bukti', file)

  uploading.value = true
  try {
    await api.post(`/akun/transaksi/${uploadTarget.value.id}/pembayaran`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    toast.success('Terkirim', 'Bukti pembayaran menunggu verifikasi admin.')
    showUpload.value = false
    fetchBooking()
  } catch (e) {
    const data = e.response?.data
    uploadErrors.value = data?.errors || {}
    uploadError.value  = data?.message || 'Gagal mengirim bukti pembayaran.'
  } finally {
    uploading.value = false
  }
}

async function batal(t) {
  if (!confirm('Batalkan booking ini?')) return
  try {
    await api.patch(`/akun/transaksi/${t.id}/batal`)
    toast.success('Dibatalkan', 'Booking berhasil dibatalkan.')
    fetchBooking()
  } catch (e) {
    toast.error('Gagal', e.response?.data?.message || 'Gagal membatalkan booking.')
  }
}

watch(page, fetchBooking)

onMounted(() => {
  fetchBooking()
  fetchRekening()
})
</script>
