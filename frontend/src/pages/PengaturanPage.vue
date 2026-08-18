<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-headline-lg font-headline-lg font-bold text-slate-900 dark:text-white">Pengaturan</h1>
        <p class="text-body-md text-slate-500 dark:text-slate-400 mt-1">Kelola profil administrator dan preferensi sistem SewaKen.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Section A: Profil Admin -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/80 p-6 shadow-sm">
        <h2 class="text-title-md font-bold mb-4 text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">Profil Administrator</h2>
        
        <form @submit.prevent="saveProfile" class="space-y-4">
          <div class="flex flex-col items-center mb-6">
            <div 
              class="w-24 h-24 rounded-full bg-slate-100 dark:bg-slate-700 border-2 border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center cursor-pointer overflow-hidden relative group"
              @click="$refs.fileInput.click()"
              @dragover.prevent
              @drop.prevent="handleDrop"
            >
              <img v-if="profileForm.preview" :src="profileForm.preview" class="w-full h-full object-cover" />
              <div v-else class="text-center text-slate-400 dark:text-slate-500">
                <span class="material-symbols-outlined text-3xl">add_a_photo</span>
              </div>
              <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center transition-all">
                <span class="material-symbols-outlined text-white">upload</span>
              </div>
            </div>
            <input type="file" ref="fileInput" class="hidden" @change="handleFileChange" accept="image/*" />
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Klik atau seret foto profil baru</p>
          </div>

          <FormField label="Nama Lengkap">
            <input v-model="profileForm.name" type="text" class="form-input" required />
          </FormField>
          
          <FormField label="Email">
            <input v-model="profileForm.email" type="email" class="form-input" required />
          </FormField>
          
          <FormField label="No. Telepon / WhatsApp">
            <input v-model="profileForm.telepon" type="tel" class="form-input" />
          </FormField>

          <div class="flex justify-end pt-2">
            <button type="submit" class="px-5 py-2.5 bg-secondary text-on-secondary font-bold rounded-lg hover:bg-secondary-container transition shadow-sm disabled:opacity-50" :disabled="loadingProfile">
              <span v-if="loadingProfile" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
              Simpan Profil
            </button>
          </div>
        </form>
      </div>

      <!-- Section C: Ubah Password -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/80 p-6 shadow-sm">
        <h2 class="text-title-md font-bold mb-4 text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">Keamanan & Password</h2>
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
            <button type="submit" class="px-5 py-2.5 bg-secondary text-on-secondary font-bold rounded-lg hover:bg-secondary-container disabled:opacity-50 shadow-sm transition" :disabled="loadingPassword">
              <span v-if="loadingPassword" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
              Ganti Password
            </button>
          </div>
        </form>
      </div>

      <!-- Section B: Pengaturan Aplikasi -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700/80 p-6 shadow-sm lg:col-span-2">
        <h2 class="text-title-md font-bold mb-4 text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">Pengaturan Aplikasi SewaKen</h2>
        
        <form @submit.prevent="saveSettings" class="space-y-4 max-w-2xl">
          <FormField label="Nama Aplikasi / Bisnis">
            <input v-model="settingsForm.app_name" type="text" class="form-input" required />
          </FormField>
          
          <FormField label="Deskripsi / Tagline">
            <textarea v-model="settingsForm.app_description" rows="3" class="form-input resize-none"></textarea>
          </FormField>
          
          <FormField label="URL Logo (Opsional)">
            <input v-model="settingsForm.app_logo" type="text" class="form-input" placeholder="https://..." />
          </FormField>

          <div class="flex justify-end pt-2">
            <button type="submit" class="px-5 py-2.5 bg-secondary text-on-secondary font-bold rounded-lg hover:bg-secondary-container transition shadow-sm disabled:opacity-50" :disabled="loadingSettings">
              <span v-if="loadingSettings" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
              Simpan Pengaturan
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

const auth = useAuthStore()
const toast = useToastStore()

// Profile State
const loadingProfile = ref(false)
const profileForm = reactive({
  name: auth.user?.name || '',
  email: auth.user?.email || '',
  telepon: auth.user?.telepon || '',
  foto_profil: null,
  preview: auth.user?.foto_profil || null
})

// Settings State
const loadingSettings = ref(false)
const settingsForm = reactive({
  app_name: 'SewaKen',
  app_description: 'Sistem Sewa Kendaraan',
  app_logo: ''
})

// Password State
const loadingPassword = ref(false)
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    profileForm.foto_profil = file
    profileForm.preview = URL.createObjectURL(file)
  }
}

const handleDrop = (e) => {
  const file = e.dataTransfer.files[0]
  if (file && file.type.startsWith('image/')) {
    profileForm.foto_profil = file
    profileForm.preview = URL.createObjectURL(file)
  }
}

const saveProfile = async () => {
  loadingProfile.value = true
  try {
    const formData = new FormData()
    formData.append('_method', 'PATCH')
    formData.append('name', profileForm.name)
    formData.append('email', profileForm.email)
    if (profileForm.telepon) formData.append('telepon', profileForm.telepon)
    if (profileForm.foto_profil) formData.append('foto_profil', profileForm.foto_profil)

    await api.post('/auth/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    auth.user.name = profileForm.name
    auth.user.email = profileForm.email
    auth.user.telepon = profileForm.telepon
    if (profileForm.preview) auth.user.foto_profil = profileForm.preview
    
    toast.success('Berhasil', 'Profil berhasil diperbarui.')
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Gagal memperbarui profil.')
  } finally {
    loadingProfile.value = false
  }
}

const savePassword = async () => {
  if (passwordForm.password !== passwordForm.password_confirmation) {
    toast.error('Peringatan', 'Password baru dan konfirmasi tidak cocok.')
    return
  }
  
  loadingPassword.value = true
  try {
    await api.patch('/auth/password', passwordForm)
    toast.success('Berhasil', 'Password berhasil diubah.')
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Gagal mengubah password.')
  } finally {
    loadingPassword.value = false
  }
}

const fetchSettings = async () => {
  try {
    const res = await api.get('/settings')
    const data = res.data.data || res.data
    if (Array.isArray(data)) {
      data.forEach(item => {
        if (settingsForm[item.key] !== undefined) {
          settingsForm[item.key] = item.value
        }
      })
    } else if (data) {
      Object.assign(settingsForm, data)
    }
  } catch (error) {
    console.error('Failed to fetch settings', error)
  }
}

const saveSettings = async () => {
  loadingSettings.value = true
  try {
    await api.patch('/settings', settingsForm)
    toast.success('Berhasil', 'Pengaturan berhasil disimpan.')
  } catch (error) {
    toast.error('Gagal', error.response?.data?.message || 'Gagal menyimpan pengaturan.')
  } finally {
    loadingSettings.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>
