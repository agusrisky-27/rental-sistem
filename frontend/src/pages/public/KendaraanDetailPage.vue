<template>
  <div>
    <RouterLink to="/" class="inline-flex items-center gap-1 text-label-md text-secondary dark:text-blue-400 hover:underline mb-4">
      <span class="material-symbols-outlined icon-18">arrow_back</span>
      Kembali ke katalog
    </RouterLink>

    <div v-if="loading" class="py-16 text-center text-slate-500 dark:text-slate-400">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
      <p>Memuat detail kendaraan...</p>
    </div>

    <div v-else-if="!kendaraan" class="py-16 text-center text-slate-500 dark:text-slate-400">
      <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">search_off</span>
      Kendaraan tidak ditemukan.
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <!-- Detail + kalender -->
      <div class="lg:col-span-3 flex flex-col gap-6">
        <div class="glass-card rounded-xl overflow-hidden shadow-sm">
          <div class="aspect-[16/9] bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center overflow-hidden">
            <img v-if="kendaraan.foto_url" :src="kendaraan.foto_url" :alt="kendaraan.nama" class="w-full h-full object-cover" />
            <span v-else class="material-symbols-outlined text-slate-400 dark:text-slate-500 text-6xl">directions_car</span>
          </div>
          <div class="p-5">
            <div class="flex items-start justify-between gap-3">
              <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">{{ kendaraan.nama }}</h1>
              <StatusBadge :status="kendaraan.status" />
            </div>
            <p class="text-body-md text-slate-500 dark:text-slate-400 mt-1">
              {{ kendaraan.tipe }} · {{ kendaraan.tahun }} · {{ kendaraan.warna || '-' }} · {{ kendaraan.kapasitas }} kursi
            </p>
            <p v-if="kendaraan.deskripsi" class="text-body-md text-slate-700 dark:text-slate-300 mt-3 whitespace-pre-line">{{ kendaraan.deskripsi }}</p>
          </div>
        </div>

        <div>
          <h2 class="text-body-lg font-bold text-slate-900 dark:text-white mb-3">Ketersediaan</h2>
          <KalenderKetersediaan :jadwal="kendaraan.jadwal || []" />
        </div>
      </div>

      <!-- Form booking -->
      <div class="lg:col-span-2">
        <div class="glass-card rounded-xl p-5 shadow-sm lg:sticky lg:top-24">
          <p class="text-headline-md font-headline-md font-bold text-secondary dark:text-blue-400">
            {{ formatRupiah(kendaraan.harga) }} <span class="text-sm font-normal text-slate-500 dark:text-slate-400">/ hari</span>
          </p>

          <p v-if="auth.isAdmin" class="mt-4 text-sm text-slate-500 dark:text-slate-400">
            Booking hanya tersedia untuk akun pelanggan.
          </p>

          <form v-else @submit.prevent="submit" :novalidate="!auth.isLoggedIn" class="mt-4 flex flex-col gap-4">
            <FormField label="Tanggal Mulai" :error="fieldError('tanggal_mulai')">
              <input type="date" v-model="form.tanggal_mulai" :min="today" required class="form-input" />
            </FormField>
            <FormField label="Tanggal Akhir" :error="fieldError('tanggal_akhir')">
              <input type="date" v-model="form.tanggal_akhir" :min="minAkhir" required class="form-input" />
            </FormField>

            <div class="flex justify-between items-center text-sm border-t border-slate-200 dark:border-slate-700 pt-3">
              <span class="text-slate-500 dark:text-slate-400">Estimasi total ({{ durasi }} hari)</span>
              <span class="font-bold text-slate-900 dark:text-white">{{ formatRupiah(estimasi) }}</span>
            </div>

            <p v-if="error" class="text-sm font-medium text-rose-500">{{ error }}</p>

            <button type="submit" :disabled="submitting || kendaraan.status === 'maintenance'"
              class="bg-secondary hover:bg-secondary-container text-on-secondary font-bold py-3 rounded-lg flex justify-center items-center gap-2 transition-all shadow-sm disabled:opacity-60">
              <span v-if="submitting" class="material-symbols-outlined animate-spin icon-20">progress_activity</span>
              <span v-else>{{ auth.isLoggedIn ? 'Booking Sekarang' : 'Masuk untuk Booking' }}</span>
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Booking tanpa bukti pembayaran akan dibatalkan otomatis dalam 24 jam.
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import FormField from '@/components/ui/FormField.vue'
import KalenderKetersediaan from '@/components/ui/KalenderKetersediaan.vue'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const toast  = useToastStore()

const today = new Date().toLocaleDateString('sv-SE') // YYYY-MM-DD lokal, bukan UTC

const kendaraan  = ref(null)
const loading    = ref(false)
const submitting = ref(false)
const error      = ref('')
const errors     = ref({})
// tanggal bisa dibawa dari filter katalog (?mulai=&akhir=)
const form = ref({
  tanggal_mulai: route.query.mulai || '',
  tanggal_akhir: route.query.akhir || '',
})

const fieldError = (k) => errors.value[k]?.[0] || ''

const minAkhir = computed(() => {
  if (!form.value.tanggal_mulai) return today
  const d = new Date(form.value.tanggal_mulai + 'T00:00')
  d.setDate(d.getDate() + 1)
  return d.toLocaleDateString('sv-SE')
})

const durasi = computed(() => {
  const { tanggal_mulai: m, tanggal_akhir: a } = form.value
  if (!m || !a) return 0
  return Math.max(1, Math.round((new Date(a) - new Date(m)) / 86400000))
})
const estimasi = computed(() => (kendaraan.value?.harga || 0) * durasi.value)

function formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID') }

async function fetchKendaraan() {
  loading.value = true
  try {
    const res = await api.get(`/katalog/${route.params.id}`)
    kendaraan.value = res.data.data || res.data
  } catch (e) {
    kendaraan.value = null
  } finally {
    loading.value = false
  }
}

async function submit() {
  if (!auth.isLoggedIn) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }
  if (form.value.tanggal_akhir <= form.value.tanggal_mulai) {
    error.value = 'Tanggal akhir harus setelah tanggal mulai.'
    return
  }
  error.value = ''
  errors.value = {}
  submitting.value = true
  try {
    await api.post('/akun/transaksi', { kendaraan_id: kendaraan.value.id, ...form.value })
    toast.success('Booking berhasil', 'Silakan upload bukti pembayaran di halaman Booking Saya.')
    router.push('/akun/booking')
  } catch (e) {
    const data = e.response?.data
    errors.value = data?.errors || {}
    error.value  = data?.message || 'Gagal membuat booking.'
  } finally {
    submitting.value = false
  }
}

onMounted(fetchKendaraan)
</script>
