<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Manajemen Pelanggan</h1>
        <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Kelola direktori pelanggan dan tingkat keanggotaan SiwaKen.</p>
      </div>
      <button @click="openTambah"
        class="bg-secondary text-on-secondary px-5 py-2.5 rounded-lg font-bold text-label-md
               flex items-center gap-2 hover:bg-secondary-container transition-colors shadow-sm">
        <span class="material-symbols-outlined" style="font-size:18px">add</span>
        Tambah Pelanggan
      </button>
    </div>

    <!-- Filters -->
    <div class="filter-panel mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="flex flex-wrap gap-3 w-full md:w-auto">
        <!-- Search -->
        <div class="relative w-full md:w-80">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary dark:text-blue-400 pointer-events-none transition-colors" style="font-size:20px">search</span>
          <input v-model="search" type="text" placeholder="Cari nama, email, no hp..."
            class="search-input-field" />
          <button v-if="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <span class="material-symbols-outlined" style="font-size:18px">close</span>
          </button>
        </div>

        <!-- Level Filter -->
        <select v-model="filterLevel"
          class="form-input md:w-44 py-2.5">
          <option value="">Semua Level</option>
          <option value="Gold">Gold</option>
          <option value="Silver">Silver</option>
          <option value="Basic">Basic</option>
        </select>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto justify-end">
        <button v-if="filterLevel || search" @click="resetFilters" class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:text-rose-400 flex items-center gap-1">
          <span class="material-symbols-outlined" style="font-size:16px">restart_alt</span>
          Reset
        </button>
        <button @click="exportPelanggan" :disabled="exporting"
          class="text-secondary dark:text-blue-400 font-label-md text-label-md flex items-center gap-2
                       border border-secondary/40 dark:border-blue-500/40 px-4 py-2.5 rounded-lg hover:bg-secondary/10 dark:hover:bg-blue-500/10 transition-colors disabled:opacity-50">
          <span v-if="exporting" class="animate-spin inline-block w-4 h-4 border-2 border-secondary dark:border-blue-400 border-t-transparent rounded-full"></span>
          <span v-else class="material-symbols-outlined" style="font-size:18px">download</span>
          {{ exporting ? 'Mengekspor...' : 'Export Data' }}
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-panel">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700/80 text-slate-600 dark:text-slate-300 text-label-sm font-label-sm uppercase tracking-wider">
              <th class="px-6 py-4 font-semibold">Nama Pelanggan</th>
              <th class="px-6 py-4 font-semibold">Email & Telepon</th>
              <th class="px-6 py-4 font-semibold">Level</th>
              <th class="px-6 py-4 font-semibold text-center">Total Booking</th>
              <th class="px-6 py-4 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-body-md font-body-md">
            <tr v-if="loading">
               <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                 <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-secondary border-t-transparent mb-2"></div>
                 <p>Memuat data pelanggan...</p>
               </td>
            </tr>
            <tr v-else-if="pelanggan.length === 0">
               <td colspan="5" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                 <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block">person_off</span>
                 Belum ada data pelanggan ditemukan.
               </td>
            </tr>
            <tr v-else v-for="p in pelanggan" :key="p.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-750/50 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/60 text-secondary dark:text-blue-300
                              flex items-center justify-center font-bold text-label-md shrink-0">
                    {{ initials(p.nama) }}
                  </div>
                  <div>
                    <span class="font-semibold text-slate-900 dark:text-white block">{{ p.nama }}</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ p.alamat ? p.alamat.substring(0, 30) + '...' : '-' }}</span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-slate-900 dark:text-slate-100 font-medium">{{ p.email }}</div>
                <div class="text-slate-500 dark:text-slate-400 text-label-sm">{{ p.telepon }}</div>
              </td>
              <td class="px-6 py-4">
                <StatusBadge :status="p.level?.toLowerCase() || 'basic'" />
              </td>
              <td class="px-6 py-4 text-center font-semibold text-slate-800 dark:text-slate-200">{{ p.transaksi_count ?? 0 }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button @click="openEdit(p)" title="Edit"
                    class="p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-secondary dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px">edit</span>
                  </button>
                  <button @click="openHapus(p)" title="Hapus"
                    class="p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination" class="bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-700/80 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <span class="text-slate-500 dark:text-slate-400 text-label-sm font-label-sm">
          Menampilkan {{ ((pagination.current_page - 1) * pagination.per_page) + (pelanggan.length > 0 ? 1 : 0) }}-{{ ((pagination.current_page - 1) * pagination.per_page) + pelanggan.length }} dari {{ pagination.total }} pelanggan
        </span>
        <div class="flex gap-2">
          <button 
            :disabled="!pagination.prev_page_url" 
            @click="changePage(pagination.current_page - 1)"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-secondary dark:text-blue-400 font-bold text-label-md hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Sebelumnya
          </button>
          <span class="px-3 py-1.5 rounded-lg bg-secondary text-on-secondary font-bold text-label-md">
            {{ pagination.current_page }}
          </span>
          <button 
            :disabled="!pagination.next_page_url" 
            @click="changePage(pagination.current_page + 1)"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-secondary dark:text-blue-400 font-bold text-label-md hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Tambah/Edit -->
    <BaseModal v-model="showForm" max-width="560px">
      <template #header>
        <h2 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white">
          {{ isEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}
        </h2>
      </template>

      <form @submit.prevent="savePelanggan" class="flex flex-col gap-4">
        <FormField label="Nama Lengkap">
          <input v-model="formData.nama" type="text" placeholder="Masukkan nama lengkap" class="form-input" required />
        </FormField>
        <FormField label="Email">
          <input v-model="formData.email" type="email" placeholder="contoh@email.com" class="form-input" required />
        </FormField>
        <FormField label="No. HP">
          <input v-model="formData.telepon" type="tel" placeholder="0812xxxxxx" class="form-input" required />
        </FormField>
        <FormField label="Alamat">
          <textarea v-model="formData.alamat" rows="2" placeholder="Alamat lengkap" class="form-input resize-none"></textarea>
        </FormField>
        <FormField label="Level Keanggotaan">
          <select v-model="formData.level" class="form-input">
            <option value="Basic">Basic</option>
            <option value="Silver">Silver</option>
            <option value="Gold">Gold</option>
          </select>
        </FormField>
        <!-- Upload KTP -->
        <FormField label="Upload Foto KTP (Opsional)">
          <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-6 flex flex-col
                      items-center gap-2 bg-slate-50 dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-900
                      transition-colors cursor-pointer relative overflow-hidden"
               @click="$refs.ktpInput.click()"
               @dragover.prevent
               @drop.prevent="handleKtpDrop">
            <img v-if="formData.ktpPreview" :src="formData.ktpPreview" class="absolute inset-0 w-full h-full object-cover z-10" />
            <div v-if="formData.ktpPreview" class="absolute inset-0 bg-black/50 z-20 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
              <span class="material-symbols-outlined text-white">upload</span>
            </div>
            <template v-if="!formData.ktpPreview">
              <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-secondary dark:text-blue-400">
                <span class="material-symbols-outlined">add_photo_alternate</span>
              </div>
              <p class="text-label-md font-label-md text-center text-slate-700 dark:text-slate-300">
                <span class="text-secondary dark:text-blue-400 font-semibold">Klik untuk unggah</span> atau seret file ke sini
              </p>
              <p class="text-label-sm font-label-sm text-slate-400 dark:text-slate-500">PNG, JPG · maks 5MB</p>
            </template>
            <input type="file" ref="ktpInput" class="hidden" @change="handleKtpChange" accept="image/jpeg, image/png" />
          </div>
        </FormField>
      </form>

      <template #footer>
        <button @click="showForm = false"
          class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700
                 hover:bg-slate-100 dark:hover:bg-slate-600 text-label-md font-semibold transition-colors">
          Batal
        </button>
        <button @click="savePelanggan" :disabled="saving"
          class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md flex items-center
                 font-bold hover:bg-secondary-container transition-colors shadow-sm disabled:opacity-50">
          <span v-if="saving" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
          {{ isEdit ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}
        </button>
      </template>
    </BaseModal>

    <!-- Modal Hapus -->
    <BaseModal v-model="showHapus" max-width="400px">
      <template #header>
        <div></div>
      </template>
      <div class="flex flex-col items-center text-center py-4">
        <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center mb-4">
          <span class="material-symbols-outlined text-rose-600 dark:text-rose-400 fill" style="font-size:32px">delete</span>
        </div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Hapus Pelanggan?</h2>
        <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mb-6">
          Data <strong class="text-slate-800 dark:text-slate-200">{{ selectedPelanggan?.nama }}</strong> akan dihapus secara permanen.
        </p>
        <div class="flex w-full gap-3">
          <button @click="showHapus = false"
            class="flex-1 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 rounded-lg
                   text-label-md font-semibold hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
            Batal
          </button>
          <button @click="deletePelanggan"
            class="flex-1 py-2.5 bg-rose-600 text-white rounded-lg
                   text-label-md font-semibold hover:bg-rose-700 transition-colors shadow-sm">
            Hapus
          </button>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useToastStore } from '@/stores/toast'
import api from '@/services/api'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import BaseModal   from '@/components/ui/BaseModal.vue'
import FormField   from '@/components/ui/FormField.vue'

const toast = useToastStore()
const showForm = ref(false)
const isEdit   = ref(false)
const filterLevel = ref('')
const search = ref('')

const emptyForm = () => ({ 
  nama: '', 
  email: '', 
  telepon: '', 
  alamat: '', 
  level: 'Basic', 
  foto_ktp: null, 
  ktpPreview: null 
})
const formData  = ref(emptyForm())

const pelanggan = ref([])
const loading = ref(false)
const saving = ref(false)
const exporting = ref(false)
const showHapus = ref(false)
const selectedPelanggan = ref(null)
const currentPage = ref(1)
const pagination = ref(null)

function resetFilters() {
  search.value = ''
  filterLevel.value = ''
}

const fetchPelanggan = async () => {
  loading.value = true
  try {
    const res = await api.get('/pelanggan', {
      params: {
        search: search.value,
        level: filterLevel.value,
        page: currentPage.value
      }
    })
    pelanggan.value = res.data.data
    pagination.value = res.data
  } catch (error) {
    toast.error('Gagal', 'Tidak dapat memuat data pelanggan.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchPelanggan)

let debounceTimer
watch([search, filterLevel], () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    currentPage.value = 1
    fetchPelanggan()
  }, 300)
})

const changePage = (page) => {
  currentPage.value = page
  fetchPelanggan()
}

function initials(name) {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase()
}

function handleKtpChange(e) {
  const file = e.target.files[0]
  if (file && file.type.startsWith('image/')) {
    formData.value.foto_ktp = file
    formData.value.ktpPreview = URL.createObjectURL(file)
  }
}

function handleKtpDrop(e) {
  const file = e.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) {
    formData.value.foto_ktp = file
    formData.value.ktpPreview = URL.createObjectURL(file)
  }
}

function openTambah() { 
  isEdit.value = false 
  formData.value = emptyForm() 
  showForm.value = true 
}

function openEdit(p) { 
  isEdit.value = true
  const { foto_ktp, foto_ktp_url, ...rest } = p
  formData.value = { 
    ...rest, 
    foto_ktp: null, 
    ktpPreview: foto_ktp_url || null 
  }
  showForm.value = true 
}

function openHapus(p) {
  selectedPelanggan.value = p
  showHapus.value = true
}

async function savePelanggan() {
  if (!formData.value.nama || !formData.value.email) {
    toast.error('Error', 'Nama dan Email wajib diisi.')
    return
  }

  saving.value = true
  try {
    const data = new FormData()
    Object.keys(formData.value).forEach(key => {
      if (key !== 'ktpPreview' && formData.value[key] !== null && formData.value[key] !== undefined) {
        data.append(key, formData.value[key])
      }
    })

    if (isEdit.value) {
      data.append('_method', 'PUT')
      await api.post(`/pelanggan/${formData.value.id}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      toast.success('Berhasil', 'Data pelanggan diperbarui.')
    } else {
      await api.post('/pelanggan', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      toast.success('Berhasil', 'Pelanggan baru ditambahkan.')
    }
    showForm.value = false
    fetchPelanggan()
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Tidak dapat menyimpan data pelanggan.')
  } finally {
    saving.value = false
  }
}

async function deletePelanggan() {
  if (!selectedPelanggan.value) return
  try {
    await api.delete(`/pelanggan/${selectedPelanggan.value.id}`)
    toast.success('Berhasil', `Pelanggan ${selectedPelanggan.value.nama} berhasil dihapus.`)
    showHapus.value = false
    if (pelanggan.value.length === 1 && currentPage.value > 1) {
      currentPage.value--
    }
    fetchPelanggan()
  } catch (error) {
    toast.error('Gagal', 'Tidak dapat menghapus pelanggan.')
  }
}

async function exportPelanggan() {
  exporting.value = true
  try {
    const res = await api.get('/pelanggan', {
      params: {
        search: search.value,
        level: filterLevel.value,
        limit: 1000
      }
    })
    const dataToExport = res.data.data || res.data
    const headers = ['ID', 'Nama', 'Email', 'Telepon', 'Alamat', 'Level', 'Total Booking']
    const rows = dataToExport.map(p => [
      p.id,
      `"${p.nama || '-'}"`,
      `"${p.email || '-'}"`,
      `"${p.telepon || '-'}"`,
      `"${p.alamat || '-'}"`,
      p.level,
      p.transaksi_count ?? p.transaksi?.length ?? 0
    ])
    const csvContent = [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `pelanggan_export_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    toast.success('Berhasil', 'Data pelanggan berhasil diekspor.')
  } catch (error) {
    toast.error('Gagal', 'Gagal mengekspor data pelanggan.')
  } finally {
    exporting.value = false
  }
}
</script>
