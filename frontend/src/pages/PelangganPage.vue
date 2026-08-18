<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Manajemen Pelanggan</h1>
        <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Kelola direktori pelanggan dan tingkat keanggotaan SewaKen.</p>
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
        <button class="text-secondary dark:text-blue-400 font-label-md text-label-md flex items-center gap-2
                       border border-secondary/40 dark:border-blue-500/40 px-4 py-2.5 rounded-lg hover:bg-secondary/10 dark:hover:bg-blue-500/10 transition-colors">
          <span class="material-symbols-outlined" style="font-size:18px">download</span>
          Export Data
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
              <td class="px-6 py-4 text-center font-semibold text-slate-800 dark:text-slate-200">{{ p.transaksi?.length || 0 }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button @click="openEdit(p)" title="Edit"
                    class="p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:text-secondary dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px">edit</span>
                  </button>
                  <button @click="deletePelanggan(p.id)" title="Hapus"
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
            Prev
          </button>
          <span class="px-3 py-1.5 rounded-lg bg-secondary text-on-secondary font-bold text-label-md">
            {{ pagination.current_page }}
          </span>
          <button 
            :disabled="!pagination.next_page_url" 
            @click="changePage(pagination.current_page + 1)"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-secondary dark:text-blue-400 font-bold text-label-md hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Next
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
                      transition-colors cursor-pointer">
            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-secondary dark:text-blue-400">
              <span class="material-symbols-outlined">add_photo_alternate</span>
            </div>
            <p class="text-label-md font-label-md text-center text-slate-700 dark:text-slate-300">
              <span class="text-secondary dark:text-blue-400 font-semibold">Klik untuk unggah</span> atau seret file ke sini
            </p>
            <p class="text-label-sm font-label-sm text-slate-400 dark:text-slate-500">PNG, JPG · maks 5MB</p>
          </div>
        </FormField>
      </form>

      <template #footer>
        <button @click="showForm = false"
          class="px-5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700
                 hover:bg-slate-100 dark:hover:bg-slate-600 text-label-md font-semibold transition-colors">
          Batal
        </button>
        <button @click="savePelanggan"
          class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md
                 font-bold hover:bg-secondary-container transition-colors shadow-sm">
          {{ isEdit ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}
        </button>
      </template>
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
const filterLevel  = ref('')
const search = ref('')

const emptyForm = () => ({ nama:'', email:'', telepon:'', alamat:'', level:'Basic' })
const formData  = ref(emptyForm())

const pelanggan = ref([])
const loading = ref(false)
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
function openTambah() { isEdit.value = false; formData.value = emptyForm(); showForm.value = true }
function openEdit(p)  { isEdit.value = true;  formData.value = { ...p };  showForm.value = true }

async function savePelanggan() {
  if (!formData.value.nama || !formData.value.email) {
    toast.error('Error', 'Nama dan Email wajib diisi.')
    return
  }

  try {
    if (isEdit.value) {
      await api.put(`/pelanggan/${formData.value.id}`, formData.value)
      toast.success('Berhasil', 'Data pelanggan diperbarui.')
    } else {
      await api.post('/pelanggan', formData.value)
      toast.success('Berhasil', 'Pelanggan baru ditambahkan.')
    }
    showForm.value = false
    fetchPelanggan()
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Tidak dapat menyimpan data pelanggan.')
  }
}

async function deletePelanggan(id) {
  if (confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')) {
    try {
      await api.delete(`/pelanggan/${id}`)
      toast.success('Berhasil', 'Pelanggan dihapus.')
      if (pelanggan.value.length === 1 && currentPage.value > 1) {
        currentPage.value--
      }
      fetchPelanggan()
    } catch (error) {
      toast.error('Gagal', 'Tidak dapat menghapus pelanggan.')
    }
  }
}
</script>
