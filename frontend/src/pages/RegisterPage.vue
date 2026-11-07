<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex items-center justify-center p-4 transition-colors">
    <div class="w-full max-w-md">
      <div class="bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-xl border border-slate-200 dark:border-slate-800 flex flex-col gap-6 transition-colors">
        <div class="text-center">
          <img src="/logo.png" alt="SewaKen Logo" class="w-16 h-16 object-contain mx-auto mb-3" />
          <h1 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white mb-1">Daftar Akun SewaKen</h1>
          <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400">Buat akun untuk mulai menyewa kendaraan.</p>
        </div>

        <form @submit.prevent="handleRegister" class="flex flex-col gap-4">
          <FormField label="Nama Lengkap" :error="fieldError('name')">
            <input v-model="form.name" type="text" required class="form-input py-3" />
          </FormField>
          <FormField label="Email" :error="fieldError('email')">
            <input v-model="form.email" type="email" required placeholder="nama@email.com" class="form-input py-3" />
          </FormField>
          <FormField label="No. Telepon / WhatsApp" :error="fieldError('telepon')">
            <input v-model="form.telepon" type="tel" required class="form-input py-3" />
          </FormField>
          <FormField label="Password" :error="fieldError('password')">
            <input v-model="form.password" type="password" required minlength="8" class="form-input py-3" />
          </FormField>
          <FormField label="Konfirmasi Password">
            <input v-model="form.password_confirmation" type="password" required class="form-input py-3" />
          </FormField>

          <button type="submit" :disabled="loading"
            class="mt-2 bg-secondary hover:bg-secondary-container text-on-secondary font-bold
                   py-3.5 rounded-lg flex justify-center items-center gap-2 transition-all
                   shadow-sm disabled:opacity-60">
            <span v-if="loading" class="material-symbols-outlined animate-spin icon-20">progress_activity</span>
            <span v-else>Daftar</span>
          </button>

          <p v-if="error" class="text-sm font-medium text-rose-500 text-center">{{ error }}</p>

          <p class="text-sm text-center text-slate-500 dark:text-slate-400">
            Sudah punya akun?
            <RouterLink to="/login" class="text-secondary dark:text-blue-400 font-semibold hover:underline">Masuk</RouterLink>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import FormField from '@/components/ui/FormField.vue'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()

const form    = ref({ name: '', email: '', telepon: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error   = ref('')
const errors  = ref({})

const fieldError = (k) => errors.value[k]?.[0] || ''

async function handleRegister() {
  error.value   = ''
  errors.value  = {}
  loading.value = true
  try {
    await auth.register(form.value)
    router.push(route.query.redirect || auth.homePath)
  } catch (e) {
    const data = e.response?.data
    errors.value = data?.errors || {}
    error.value  = data?.errors ? '' : (data?.message || 'Pendaftaran gagal.')
  } finally {
    loading.value = false
  }
}
</script>
