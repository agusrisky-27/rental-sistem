<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-headline-sm font-headline-sm font-bold text-primary dark:text-white">Pengaturan</h1>
        <p class="text-body-md text-on-surface-variant dark:text-gray-400 mt-1">Kelola profil admin dan pengaturan aplikasi.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Section A: Profil Admin -->
      <div class="bg-surface dark:bg-gray-800 rounded-xl border border-outline-variant dark:border-gray-700 p-6">
        <h2 class="text-title-md font-bold mb-4 dark:text-white border-b pb-2 dark:border-gray-700">Profil Admin</h2>
        
        <form @submit.prevent="saveProfile" class="space-y-4">
          <div class="flex flex-col items-center mb-6">
            <div 
              class="w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center cursor-pointer overflow-hidden relative group"
              @click="$refs.fileInput.click()"
              @dragover.prevent
              @drop.prevent="handleDrop"
            >
              <img v-if="profileForm.preview" :src="profileForm.preview" class="w-full h-full object-cover" />
              <div v-else class="text-center text-gray-400 dark:text-gray-500">
                <span class="material-symbols-rounded">add_a_photo</span>
              </div>
              <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center transition-all">
                <span class="material-symbols-rounded text-white">upload</span>
              </div>
            </div>
            <input type="file" ref="fileInput" class="hidden" @change="handleFileChange" accept="image/*" />
            <p class="text-xs text-gray-500 mt-2">Klik atau drag & drop foto</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Nama</label>
            <input v-model="profileForm.name" type="text" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Email</label>
            <input v-model="profileForm.email" type="email" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">No HP</label>
            <input v-model="profileForm.telepon" type="tel" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          </div>

          <div class="flex justify-end pt-4">
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition disabled:opacity-50" :disabled="loadingProfile">
              <span v-if="loadingProfile" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>

      <!-- Section C: Ubah Password -->
      <div class="bg-surface dark:bg-gray-800 rounded-xl border border-outline-variant dark:border-gray-700 p-6">
        <h2 class="text-title-md font-bold mb-4 dark:text-white border-b pb-2 dark:border-gray-700">Ubah Password</h2>
        <form @submit.prevent="savePassword" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Password Lama</label>
            <input v-model="passwordForm.current_password" type="password" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Password Baru</label>
            <input v-model="passwordForm.password" type="password" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Konfirmasi Password Baru</label>
            <input v-model="passwordForm.password_confirmation" type="password" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
          </div>
          
          <div class="flex justify-end pt-4">
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 disabled:opacity-50" :disabled="loadingPassword">
              <span v-if="loadingPassword" class="animate-spin inline-block mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
              Simpan Password
            </button>
          </div>
        </form>
      </div>

      <!-- Section B: Pengaturan Aplikasi -->
      <div class="bg-surface dark:bg-gray-800 rounded-xl border border-outline-variant dark:border-gray-700 p-6">
        <h2 class="text-title-md font-bold mb-4 dark:text-white border-b pb-2 dark:border-gray-700">Pengaturan Aplikasi</h2>
        
        <form @submit.prevent="saveSettings" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Nama Aplikasi</label>
            <input v-model="settingsForm.app_name" type="text" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Deskripsi</label>
            <textarea v-model="settingsForm.app_description" rows="3" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Logo URL</label>
            <input v-model="settingsForm.app_logo" type="text" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="https://..." />
          </div>

          <div class="flex justify-end pt-4">
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition disabled:opacity-50" :disabled="loadingSettings">
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
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

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
    formData.append('_method', 'PATCH') // for Laravel
    formData.append('name', profileForm.name)
    formData.append('email', profileForm.email)
    if (profileForm.telepon) formData.append('telepon', profileForm.telepon)
    if (profileForm.foto_profil) formData.append('foto_profil', profileForm.foto_profil)

    await axios.post('http://localhost:8000/api/auth/profile', formData, {
      headers: { 
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'multipart/form-data'
      }
    })
    
    // Update local user state
    auth.user.name = profileForm.name
    auth.user.email = profileForm.email
    auth.user.telepon = profileForm.telepon
    if (profileForm.preview) auth.user.foto_profil = profileForm.preview
    
    alert('Profil berhasil diperbarui')
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal memperbarui profil')
  } finally {
    loadingProfile.value = false
  }
}

const savePassword = async () => {
  if (passwordForm.password !== passwordForm.password_confirmation) {
    return alert('Password baru dan konfirmasi tidak cocok')
  }
  
  loadingPassword.value = true
  try {
    await axios.patch('http://localhost:8000/api/auth/password', passwordForm, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
    })
    alert('Password berhasil diubah')
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal mengubah password')
  } finally {
    loadingPassword.value = false
  }
}

const fetchSettings = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/settings')
    const data = res.data.data || res.data
    // Assuming backend returns { app_name: '...', app_description: '...' }
    // or array of key-value objects
    if (Array.isArray(data)) {
      data.forEach(item => {
        if (settingsForm[item.key] !== undefined) {
          settingsForm[item.key] = item.value
        }
      })
    } else {
      Object.assign(settingsForm, data)
    }
  } catch (error) {
    console.error('Failed to fetch settings', error)
  }
}

const saveSettings = async () => {
  loadingSettings.value = true
  try {
    await axios.patch('http://localhost:8000/api/settings', settingsForm, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
    })
    alert('Pengaturan berhasil disimpan')
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menyimpan pengaturan')
  } finally {
    loadingSettings.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>
