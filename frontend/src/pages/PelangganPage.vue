<template>
  <div>
    <div class="flex justify-between items-end mb-8">
      <div>
        <h1 class="text-headline-lg font-headline-lg text-on-surface mb-2">Manajemen Pelanggan</h1>
        <p class="text-on-surface-variant font-body-md text-body-md">Kelola data pelanggan SewaKen.</p>
      </div>
      <button @click="openTambah"
        class="bg-secondary text-on-primary px-6 py-3 rounded-lg font-bold text-label-md
               flex items-center gap-2 hover:bg-secondary-container transition-colors shadow-sm">
        <span class="material-symbols-outlined">add</span>
        Tambah Pelanggan
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-surface rounded-xl shadow-sm border border-outline-variant p-4 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="flex flex-wrap gap-4 w-full md:w-auto">
        <div class="relative w-full md:w-64">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size:18px">search</span>
          <input v-model="search" type="text" placeholder="Cari pelanggan..."
            class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg
                   text-body-md font-body-md focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary/50 transition-all" />
        </div>
        <select v-model="filterLevel"
          class="bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2
                 text-body-md font-body-md focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary/50 transition-all">
          <option value="">Semua Level</option>
          <option value="Gold">Gold</option>
          <option value="Silver">Silver</option>
          <option value="Basic">Basic</option>
        </select>
      </div>
      <button class="text-secondary font-label-md text-label-md flex items-center gap-2
                     border border-secondary px-4 py-2 rounded-lg hover:bg-secondary-container/10 transition-all">
        <span class="material-symbols-outlined" style="font-size:18px">download</span>
        Export Data
      </button>
    </div>

    <!-- Table -->
    <div class="bg-surface rounded-xl shadow-sm border border-outline-variant overflow-hidden">
      <table class="w-full text-left">
        <thead>
          <tr class="bg-surface-container-low border-b border-outline-variant text-on-surface-variant text-label-sm font-label-sm uppercase tracking-wider">
            <th class="px-6 py-4 font-semibold">Nama Pelanggan</th>
            <th class="px-6 py-4 font-semibold">Email & Telepon</th>
            <th class="px-6 py-4 font-semibold">Level</th>
            <th class="px-6 py-4 font-semibold text-center">Total Booking</th>
            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-variant">
          <tr v-if="loading">
             <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">Memuat data...</td>
          </tr>
          <tr v-else-if="pelanggan.length === 0">
             <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">Belum ada data pelanggan.</td>
          </tr>
          <tr v-else v-for="p in pelanggan" :key="p.id" class="hover:bg-surface-container-lowest transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-secondary-fixed-dim text-secondary
                            flex items-center justify-center font-bold text-label-md">
                  {{ initials(p.nama) }}
                </div>
                <span class="font-semibold text-on-surface">{{ p.nama }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-on-surface">{{ p.email }}</div>
              <div class="text-on-surface-variant text-label-sm font-label-sm">{{ p.telepon }}</div>
            </td>
            <td class="px-6 py-4">
              <StatusBadge :status="p.level?.toLowerCase() || 'basic'" />
            </td>
            <td class="px-6 py-4 text-center font-semibold text-on-surface">{{ p.transaksi?.length || '-' }}</td>
            <td class="px-6 py-4 text-right">
              <button class="text-secondary hover:text-secondary-container p-2 transition-colors">
                <span class="material-symbols-outlined">visibility</span>
              </button>
              <button @click="openEdit(p)" class="text-on-surface-variant hover:text-on-surface p-2 transition-colors">
                <span class="material-symbols-outlined">edit</span>
              </button>
              <button @click="deletePelanggan(p.id)" class="text-error hover:text-error/80 p-2 transition-colors">
                <span class="material-symbols-outlined">delete</span>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination -->
      <div v-if="pagination" class="bg-surface border-t border-outline-variant px-6 py-4 flex items-center justify-between">
        <span class="text-on-surface-variant text-label-sm font-label-sm">
          Menampilkan {{ ((pagination.current_page - 1) * pagination.per_page) + (pelanggan.length > 0 ? 1 : 0) }}-{{ ((pagination.current_page - 1) * pagination.per_page) + pelanggan.length }} dari {{ pagination.total }} pelanggan
        </span>
        <div class="flex gap-2">
          <button 
            :disabled="!pagination.prev_page_url" 
            @click="changePage(pagination.current_page - 1)"
            class="px-3 py-1 rounded-lg text-secondary font-bold text-label-md disabled:opacity-50">
            Prev
          </button>
          <button 
            class="px-3 py-1 rounded-lg bg-secondary text-on-primary font-bold text-label-md">
            {{ pagination.current_page }}
          </button>
          <button 
            :disabled="!pagination.next_page_url" 
            @click="changePage(pagination.current_page + 1)"
            class="px-3 py-1 rounded-lg text-secondary font-bold text-label-md disabled:opacity-50">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Tambah/Edit -->
    <BaseModal v-model="showForm" max-width="560px">
      <template #header>
        <h2 class="text-headline-md font-headline-md font-bold text-primary">
          {{ isEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}
        </h2>
      </template>

      <div class="flex flex-col gap-5">
        <FormField label="Nama Lengkap">
          <input v-model="formData.nama" type="text" placeholder="Masukkan nama lengkap" class="form-input" />
        </FormField>
        <FormField label="Email">
          <input v-model="formData.email" type="email" placeholder="contoh@email.com" class="form-input" />
        </FormField>
        <FormField label="No. HP">
          <input v-model="formData.telepon" type="tel" placeholder="0812xxxxxx" class="form-input" />
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
        <FormField label="Upload Foto KTP">
          <div class="border-2 border-dashed border-outline-variant rounded-xl p-6 flex flex-col
                      items-center gap-3 bg-surface-container-lowest hover:bg-surface-container-low
                      transition-colors cursor-pointer">
            <div class="w-12 h-12 rounded-full bg-secondary-fixed flex items-center justify-center text-secondary">
              <span class="material-symbols-outlined">add_photo_alternate</span>
            </div>
            <p class="text-label-md font-label-md text-center">
              <span class="text-secondary font-semibold">Klik untuk unggah</span> atau seret file ke sini
            </p>
            <p class="text-label-sm font-label-sm text-on-surface-variant">PNG, JPG · maks 5MB</p>
          </div>
        </FormField>
      </div>

      <template #footer>
        <button @click="showForm = false"
          class="px-5 py-2.5 rounded-lg border border-primary text-primary bg-white
                 hover:bg-surface-container text-label-md font-label-md font-semibold transition-colors">
          Batal
        </button>
        <button @click="savePelanggan"
          class="px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md
                 font-label-md font-bold hover:bg-secondary-container transition-colors shadow-sm">
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
    toast.error('Gagal', 'Tidak dapat menyimpan data pelanggan.')
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

<style>
.form-input {
  @apply w-full bg-surface-container-lowest border border-outline/40 rounded-lg px-4 py-2.5
         text-body-md font-body-md text-on-surface placeholder:text-on-surface-variant/50
         focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition-all;
}
</style>
