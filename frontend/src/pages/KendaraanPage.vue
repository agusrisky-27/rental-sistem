<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-headline-lg font-headline-lg text-primary">Manajemen Kendaraan</h1>
      <button @click="openTambah"
        class="bg-secondary text-on-secondary font-bold text-label-md px-6 py-3
               rounded-lg flex items-center gap-2 shadow-sm hover:bg-secondary-container transition-colors">
        <span class="material-symbols-outlined" style="font-size:18px">add</span>
        Tambah Kendaraan
      </button>
    </div>

    <!-- Filters -->
    <div class="glass-card rounded-xl p-4 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="flex gap-4 w-full md:w-auto">
        <div class="relative w-full md:w-64">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline"
            style="font-size:18px">search</span>
          <input v-model="search" type="text" placeholder="Cari kendaraan..."
            class="w-full pl-10 pr-4 py-2 bg-surface rounded-lg border border-outline-variant
                   focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none
                   text-body-md font-body-md transition-all" />
        </div>
        <select v-model="filterTipe"
          class="bg-surface border border-outline-variant rounded-lg px-4 py-2
                 text-body-md font-body-md focus:border-secondary outline-none">
          <option value="">Semua Tipe</option>
          <option value="SUV">SUV</option>
          <option value="Sedan">Sedan</option>
          <option value="MPV">MPV</option>
          <option value="Hatchback">Hatchback</option>
        </select>
        <select v-model="filterStatus"
          class="bg-surface border border-outline-variant rounded-lg px-4 py-2
                 text-body-md font-body-md focus:border-secondary outline-none">
          <option value="">Semua Status</option>
          <option value="tersedia">Tersedia</option>
          <option value="disewa">Disewa</option>
          <option value="maintenance">Maintenance</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-surface rounded-xl shadow-[0px_4px_20px_rgba(15,23,42,0.05)] overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-surface-variant bg-surface-container-low/50">
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Kendaraan</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Tipe</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Nomor Plat</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Harga/Hari</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold">Status</th>
            <th class="py-4 px-6 text-label-md font-label-md text-on-surface-variant font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-body-md font-body-md">
          <tr v-if="loading">
            <td colspan="6" class="text-center py-12 text-on-surface-variant">Memuat data...</td>
          </tr>
          <tr v-if="kendaraan.length === 0 && !loading">
            <td colspan="6" class="text-center py-12 text-on-surface-variant">Tidak ada data kendaraan</td>
          </tr>
          <template v-if="!loading">
            <tr v-for="k in kendaraan" :key="k.id"
              class="border-b border-surface-variant hover:bg-surface-container-lowest/50 transition-colors group">
              <td class="py-4 px-6 flex items-center gap-4">
                <div class="w-16 h-12 rounded-lg bg-surface-container overflow-hidden flex items-center justify-center">
                  <span class="material-symbols-outlined text-outline text-3xl">directions_car</span>
                </div>
                <span class="font-semibold text-primary">{{ k.nama }}</span>
              </td>
              <td class="py-4 px-6 text-on-surface">{{ k.tipe }}</td>
              <td class="py-4 px-6 text-on-surface">{{ k.plat }}</td>
              <td class="py-4 px-6 text-on-surface">{{ formatRupiah(k.harga) }}</td>
              <td class="py-4 px-6">
                <StatusBadge :status="k.status" />
              </td>
              <td class="py-4 px-6 text-right">
                <button @click="openEdit(k)"
                  class="text-on-surface-variant hover:text-secondary p-1
                         opacity-0 group-hover:opacity-100 transition-opacity">
                  <span class="material-symbols-outlined">edit</span>
                </button>
                <button @click="openHapus(k)"
                  class="text-on-surface-variant hover:text-error p-1 ml-2
                         opacity-0 group-hover:opacity-100 transition-opacity">
                  <span class="material-symbols-outlined">delete</span>
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="py-4 px-6 border-t border-surface-variant flex justify-between items-center bg-surface-container-lowest/30">
        <span class="text-label-sm font-label-sm text-on-surface-variant">
          Menampilkan {{ kendaraan.length }} dari {{ pagination?.total || 0 }} kendaraan
        </span>
        <div class="flex gap-2 items-center">
          <button @click="currentPage > 1 && (currentPage--, fetchKendaraan())" :disabled="currentPage <= 1" class="p-2 border border-outline-variant rounded-md text-on-surface-variant hover:bg-surface-container-low disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="material-symbols-outlined" style="font-size:18px">chevron_left</span>
          </button>
          <span class="px-3 py-2 text-label-md">{{ currentPage }} / {{ pagination?.last_page || 1 }}</span>
          <button @click="currentPage < pagination?.last_page && (currentPage++, fetchKendaraan())" :disabled="currentPage >= (pagination?.last_page || 1)" class="p-2 border border-outline-variant rounded-md text-on-surface-variant hover:bg-surface-container-low disabled:opacity-50 disabled:cursor-not-allowed">
            <span class="material-symbols-outlined" style="font-size:18px">chevron_right</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ── MODAL TAMBAH/EDIT KENDARAAN ── -->
    <BaseModal v-model="showForm" max-width="640px">
      <template #header>
        <h2 class="text-headline-md font-headline-md font-bold text-on-surface">
          {{ isEdit ? 'Edit Kendaraan' : 'Tambah Kendaraan Baru' }}
        </h2>
      </template>

      <form class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <!-- Left col -->
          <div class="space-y-4">
            <FormField label="Nama Kendaraan">
              <input v-model="formData.nama" type="text" placeholder="Contoh: Toyota Avanza G"
                class="form-input" />
            </FormField>
            <FormField label="Tipe Kendaraan">
              <select v-model="formData.tipe" class="form-input">
                <option value="" disabled>Pilih Tipe</option>
                <option>MPV</option><option>SUV</option><option>Sedan</option>
                <option>Hatchback</option><option>Minibus</option>
              </select>
            </FormField>
            <FormField label="Nomor Plat">
              <input v-model="formData.plat" type="text" placeholder="B 1234 ABC" class="form-input uppercase" />
            </FormField>
            <FormField label="Kapasitas">
              <div class="relative">
                <input v-model="formData.kapasitas" type="number" placeholder="0"
                  class="form-input pr-16" />
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-label-md">orang</span>
              </div>
            </FormField>
          </div>

          <!-- Right col -->
          <div class="space-y-4">
            <FormField label="Harga per Hari">
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-label-md">Rp</span>
                <input v-model="formData.harga" type="text" placeholder="0" class="form-input pl-10" />
              </div>
            </FormField>
            <FormField label="Tahun">
              <input v-model="formData.tahun" type="number" placeholder="2024" class="form-input" />
            </FormField>
            <FormField label="Warna">
              <input v-model="formData.warna" type="text" placeholder="Hitam Metalik" class="form-input" />
            </FormField>
            <FormField label="Status Awal">
              <select v-model="formData.status" class="form-input">
                <option value="tersedia">Tersedia</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </FormField>
          </div>
        </div>

        <!-- Deskripsi -->
        <FormField label="Deskripsi">
          <textarea v-model="formData.deskripsi" rows="3" placeholder="Fasilitas, kondisi khusus, dll."
            class="form-input resize-none"></textarea>
        </FormField>

        <!-- Upload foto -->
        <FormField label="Foto Kendaraan">
          <div class="border-2 border-dashed border-outline/40 rounded-xl bg-surface-container-lowest
                      hover:bg-surface-container-low transition-colors flex flex-col items-center
                      justify-center py-8 px-4 cursor-pointer group">
            <div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center
                        justify-center mb-3 group-hover:bg-primary-fixed transition-colors">
              <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary">cloud_upload</span>
            </div>
            <p class="text-label-md font-label-md text-on-surface font-semibold text-center mb-1">
              Klik untuk unggah atau seret file ke sini
            </p>
            <p class="text-label-sm font-label-sm text-on-surface-variant text-center">
              Format JPG, PNG · Maks 5MB · Rasio 16:9
            </p>
          </div>
        </FormField>
      </form>

      <template #footer>
        <button @click="showForm = false"
          class="px-5 py-2.5 rounded-lg border border-primary text-primary bg-white
                 hover:bg-surface-container text-label-md font-label-md font-bold transition-colors">
          Batal
        </button>
        <button @click="saveKendaraan"
          class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md
                 font-label-md font-bold hover:bg-secondary-container transition-colors shadow-sm">
          {{ isEdit ? 'Simpan Perubahan' : 'Simpan Kendaraan' }}
        </button>
      </template>
    </BaseModal>

    <!-- ── MODAL HAPUS ── -->
    <BaseModal v-model="showHapus" max-width="400px">
      <template #header>
        <div></div>
      </template>
      <div class="flex flex-col items-center text-center py-4">
        <div class="w-16 h-16 rounded-full bg-error-container/30 flex items-center justify-center mb-4">
          <span class="material-symbols-outlined text-error fill" style="font-size:32px">delete</span>
        </div>
        <h2 class="text-xl font-bold text-on-surface mb-3">Hapus Kendaraan?</h2>
        <p class="text-body-md font-body-md text-on-surface-variant mb-6">
          Kendaraan <strong>{{ selectedKendaraan?.nama }}</strong> akan dihapus secara permanen.
          Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="flex w-full gap-4">
          <button @click="showHapus = false"
            class="flex-1 py-3 border border-primary text-primary rounded-lg
                   text-label-md font-label-md font-semibold hover:bg-surface-container transition-colors">
            Batal
          </button>
          <button @click="deleteKendaraan"
            class="flex-1 py-3 bg-error text-on-error rounded-lg
                   text-label-md font-label-md font-semibold hover:bg-error/90 transition-colors">
            Hapus
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import BaseModal   from '@/components/ui/BaseModal.vue'
import FormField   from '@/components/ui/FormField.vue'

const toast = useToastStore()

// ── State ──
const search       = ref('')
const filterTipe   = ref('')
const filterStatus = ref('')
const showForm     = ref(false)
const showHapus    = ref(false)
const isEdit       = ref(false)
const selectedKendaraan = ref(null)

const loading = ref(false)
const currentPage = ref(1)
const pagination = ref(null)
const kendaraan = ref([])

const emptyForm = () => ({
  nama: '', tipe: '', plat: '', kapasitas: '', harga: '', tahun: '', warna: '',
  status: 'tersedia', deskripsi: '',
})
const formData = ref(emptyForm())

// ── Actions ──
async function fetchKendaraan() {
  loading.value = true
  try {
    const { data } = await api.get('/kendaraan', { 
      params: { 
        search: search.value, 
        tipe: filterTipe.value, 
        status: filterStatus.value, 
        page: currentPage.value 
      } 
    })
    kendaraan.value = data.data
    pagination.value = data
  } catch (e) {
    toast.error('Error', 'Gagal memuat data kendaraan')
  } finally {
    loading.value = false
  }
}

onMounted(fetchKendaraan)

let searchTimeout
watch([search, filterTipe, filterStatus], () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchKendaraan()
  }, 300)
})

function openTambah() {
  isEdit.value   = false
  formData.value = emptyForm()
  showForm.value = true
}

function openEdit(k) {
  isEdit.value   = true
  formData.value = { ...k }
  showForm.value = true
}

function openHapus(k) {
  selectedKendaraan.value = k
  showHapus.value = true
}

async function saveKendaraan() {
  try {
    if (isEdit.value) {
      await api.put(`/kendaraan/${formData.value.id}`, formData.value)
      toast.success('Berhasil', 'Data kendaraan berhasil diperbarui.')
    } else {
      await api.post('/kendaraan', formData.value)
      toast.success('Berhasil', 'Kendaraan berhasil ditambahkan.')
    }
    showForm.value = false
    fetchKendaraan()
  } catch (e) {
    toast.error('Error', e.response?.data?.message || 'Gagal menyimpan data.')
  }
}

async function deleteKendaraan() {
  try {
    await api.delete(`/kendaraan/${selectedKendaraan.value.id}`)
    toast.success('Dihapus', `${selectedKendaraan.value.nama} berhasil dihapus.`)
    showHapus.value = false
    fetchKendaraan()
  } catch (e) {
    toast.error('Error', 'Gagal menghapus kendaraan.')
  }
}

function formatRupiah(n) {
  return 'Rp ' + Number(n).toLocaleString('id-ID')
}
</script>

<style>
.form-input {
  @apply w-full bg-surface-container-lowest border border-outline/40 rounded-lg px-4 py-2.5
         text-body-md font-body-md text-on-surface placeholder:text-on-surface-variant/50
         focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all;
}
</style>
