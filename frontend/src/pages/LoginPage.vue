<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex items-center justify-center p-4 transition-colors">
    <div class="w-full max-w-md">
      <div class="bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-xl border border-slate-200 dark:border-slate-800 flex flex-col gap-6 transition-colors">

        <!-- Header -->
        <div class="text-center">
          <img src="/logo.png" alt="SewaKen Logo" class="w-16 h-16 object-contain mx-auto mb-3" />
          <h1 class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white mb-1">
            Selamat Datang di SewaKen
          </h1>
          <p class="text-body-md font-body-md text-slate-500 dark:text-slate-400">
            Solusi Sewa Kendaraan Terpercaya
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
          <!-- Email -->
          <div class="flex flex-col gap-1.5">
            <label class="text-label-md font-medium text-slate-700 dark:text-slate-300">Email</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 icon-18">mail</span>
              <input v-model="form.email" type="email" placeholder="nama@email.com" required
                class="form-input pl-10 py-3" />
            </div>
          </div>

          <!-- Password -->
          <div class="flex flex-col gap-1.5">
            <label class="text-label-md font-medium text-slate-700 dark:text-slate-300">Password</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 icon-18">lock</span>
              <input v-model="form.password" type="password" placeholder="••••••••" required
                class="form-input pl-10 py-3" />
            </div>
          </div>

          <!-- Remember / Forgot -->
          <div class="flex justify-between items-center text-sm">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.remember"
                class="rounded border-slate-300 dark:border-slate-600 text-secondary focus:ring-secondary/30" />
              <span class="text-slate-600 dark:text-slate-400">Ingat saya</span>
            </label>
            <a href="#" class="text-secondary dark:text-blue-400 hover:underline">
              Lupa Password?
            </a>
          </div>

          <!-- Submit -->
          <button type="submit" :disabled="loading"
            class="mt-2 bg-secondary hover:bg-secondary-container text-on-secondary font-bold
                   py-3.5 rounded-lg flex justify-center items-center gap-2 transition-all
                   shadow-sm disabled:opacity-60">
            <span v-if="loading" class="material-symbols-outlined animate-spin icon-20">progress_activity</span>
            <span v-else>Masuk Ke Dashboard</span>
            <span v-if="!loading" class="material-symbols-outlined icon-20">arrow_forward</span>
          </button>

          <!-- Error -->
          <p v-if="error" class="text-sm font-medium text-rose-500 text-center">{{ error }}</p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const form    = ref({ email: '', password: '', remember: false })
const loading = ref(false)
const error   = ref('')

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    await auth.login(form.value.email, form.value.password)
    router.push('/dashboard')
  } catch (e) {
    const data = e.response?.data
    if (data?.errors) {
      error.value = Object.values(data.errors).flat().join(' ')
    } else {
      error.value = data?.message || 'Email atau password salah.'
    }
  } finally {
    loading.value = false
  }
}
</script>
