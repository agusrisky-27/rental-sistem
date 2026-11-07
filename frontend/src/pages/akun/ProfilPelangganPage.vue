<template>
  <div>
    <div class="mb-6">
      <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Profil Saya</h1>
      <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400 mt-1">Lengkapi data diri agar proses sewa lebih lancar.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Data diri -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/80 p-6 shadow-sm">
        <h2 class="text-title-md font-bold mb-4 text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">Data Diri</h2>
        <form @submit.prevent="saveProfil" class="space-y-4">
          <FormField label="Nama Lengkap" :error="fieldError('nama')">
            <input v-model="form.nama" type="text" class="form-input" required />
          </FormField>
          <FormField label="No. Telepon / WhatsApp" :error="fieldError('telepon')">
            <input v-model="form.telepon" type="tel" class="form-input" required />
          </FormField>
          <FormField label="Alamat" :error="fieldError('alamat')">
            <textarea v-model="form.alamat" rows="3" class="form-input resize-none"></textarea>
          </FormField>
          <FormField label="Foto KTP" :error="fieldError('foto_ktp')">
            <img v-if="preview" :src="preview" alt="Foto KTP" class="w-full max-h-48 object-contain rounded-lg border border-slate-200 dark:border-slate-700 mb-2" />
            <input type="file" accept="image/*" @change="onFile" class="form-input" />
          </FormField>
          <div class="flex justify-end pt-2">
            <button type="submit" :disabled="saving"
              class="px-5 py-2.5 bg-secondary text-on-secondary font-bold rounded-lg hover:bg-secondary-container transition shadow-sm disabled:opacity-50">
              {{ saving ? 'Menyimpan...' : 'Simpan Profil' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Password -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/80 p-6 shadow-sm self-start">
        <h2 class="text-title-md font-bold mb-4 text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">Ganti Password</h2>
        <form @submit.prevent="savePassword" class="space-y-4">
          <FormField label="Password Saat Ini">
            <input v-model="passwordForm.current_password" type="password" class="form-input" required />
          </FormField>
          <FormField label="Password Baru">
            <input v-model="passwordForm.password" type="password" class="form-input" required />
          </FormField>
          <FormField label="Konfirmasi Password Baru">
            <input v-model="passwordForm.password_confirmation" type="password" class="form-input" required />
          </FormField>
          <div class="flex justify-end pt-2">
            <button type="submit" :disabled="savingPassword"
              class="px-5 py-2.5 bg-secondary text-on-secondary font-bold rounded-lg hover:bg-secondary-container transition shadow-sm disabled:opacity-50">
              {{ savingPassword ? 'Menyimpan...' : 'Ganti Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import FormField from '@/components/ui/FormField.vue'

const auth  = useAuthStore()
const toast = useToastStore()

const form    = reactive({ nama: '', telepon: '', alamat: '', foto_ktp: null })
const preview = ref(null)
const saving  = ref(false)
const errors  = ref({})

const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })
const savingPassword = ref(false)

const fieldError = (k) => errors.value[k]?.[0] || ''

function applyPelanggan(p) {
  form.nama    = p.nama || ''
  form.telepon = p.telepon || ''
  form.alamat  = p.alamat || ''
  form.foto_ktp = null
  preview.value = p.foto_ktp_url || null
}

async function fetchProfil() {
  try {
    const res = await api.get('/akun/profil')
    applyPelanggan(res.data.data || res.data)
  } catch (e) {
    toast.error('Gagal', e.response?.data?.message || 'Gagal memuat profil.')
  }
}

function onFile(e) {
  const file = e.target.files[0]
  if (file) {
    form.foto_ktp = file
    preview.value = URL.createObjectURL(file)
  }
}

async function saveProfil() {
  saving.value = true
  errors.value = {}
  try {
    const fd = new FormData()
    fd.append('nama', form.nama)
    fd.append('telepon', form.telepon)
    if (form.alamat) fd.append('alamat', form.alamat)
    if (form.foto_ktp) fd.append('foto_ktp', form.foto_ktp)
    const res = await api.post('/akun/profil', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    applyPelanggan(res.data.data || res.data)
    auth.updateUser({ name: form.nama, telepon: form.telepon })
    toast.success('Berhasil', 'Profil berhasil diperbarui.')
  } catch (e) {
    errors.value = e.response?.data?.errors || {}
    toast.error('Gagal', e.response?.data?.message || 'Gagal memperbarui profil.')
  } finally {
    saving.value = false
  }
}

async function savePassword() {
  if (passwordForm.password !== passwordForm.password_confirmation) {
    toast.error('Peringatan', 'Password baru dan konfirmasi tidak cocok.')
    return
  }
  savingPassword.value = true
  try {
    await api.patch('/auth/password', passwordForm)
    toast.success('Berhasil', 'Password berhasil diubah.')
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (e) {
    toast.error('Gagal', e.response?.data?.message || 'Gagal mengubah password.')
  } finally {
    savingPassword.value = false
  }
}

onMounted(fetchProfil)
</script>
