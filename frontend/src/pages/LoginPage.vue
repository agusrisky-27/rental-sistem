<template>
  <div class="min-h-screen bg-white flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-xl p-8 shadow-lg border border-slate-200 flex flex-col gap-6">

        <!-- Header -->
        <div class="text-center">
          <img src="/logo.png" alt="SewaKen Logo" class="w-20 h-20 object-contain mx-auto mb-4" />
          <h1 class="text-headline-md font-headline-md font-bold text-on-surface mb-1">
            Selamat Datang di SewaKen
          </h1>
          <p class="text-body-md font-body-md text-on-surface-variant">
            Solusi Sewa Kendaraan Terpercaya
          </p>
        </div>

        <!-- Form -->
        <div class="flex flex-col gap-5">
          <!-- Email -->
          <div class="flex flex-col gap-1.5">
            <label class="text-label-md font-label-md text-on-surface">Email</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline"
                style="font-size:18px">mail</span>
              <input v-model="form.email" type="email" placeholder="nama@email.com"
                class="w-full pl-10 pr-4 py-3 border border-outline-variant rounded-lg
                       bg-white text-on-surface focus:outline-none input-glow transition-all
                       placeholder:text-outline" />
            </div>
          </div>

          <!-- Password -->
          <div class="flex flex-col gap-1.5">
            <label class="text-label-md font-label-md text-on-surface">Password</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline"
                style="font-size:18px">lock</span>
              <input v-model="form.password" type="password" placeholder="••••••••"
                class="w-full pl-10 pr-4 py-3 border border-outline-variant rounded-lg
                       bg-white text-on-surface focus:outline-none input-glow transition-all
                       placeholder:text-outline" />
            </div>
          </div>

          <!-- Remember / Forgot -->
          <div class="flex justify-between items-center">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.remember"
                class="rounded border-outline-variant text-secondary focus:ring-secondary/30" />
              <span class="text-label-sm font-label-sm text-on-surface-variant">Ingat saya</span>
            </label>
            <a href="#" class="text-label-sm font-label-sm text-secondary hover:underline">
              Lupa Password?
            </a>
          </div>

          <!-- Submit -->
          <button @click="handleLogin" :disabled="loading"
            class="mt-2 bg-secondary hover:bg-secondary-container text-on-secondary font-bold
                   py-4 rounded-lg flex justify-center items-center gap-2 transition-all
                   shadow-[0px_4px_20px_rgba(0,88,190,0.3)] disabled:opacity-60">
            <span v-if="loading" class="material-symbols-outlined animate-spin">progress_activity</span>
            <span v-else>Masuk Ke Dashboard</span>
            <span v-if="!loading" class="material-symbols-outlined" style="font-size:20px">arrow_forward</span>
          </button>

          <!-- Error -->
          <p v-if="error" class="text-label-sm text-error text-center">{{ error }}</p>
        </div>
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
