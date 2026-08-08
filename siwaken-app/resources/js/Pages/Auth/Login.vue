<template>
  <div class="bg-white text-slate-900 h-screen overflow-hidden flex flex-col justify-center items-center relative antialiased" id="main-body">
    <!-- Intro Animation Container (can be replaced by Vue transitions if needed) -->
    
    <!-- Login Card Container -->
    <div class="z-10 w-full max-w-md px-4 md:px-0 mt-8" id="login-content">
      <div class="bg-white rounded-xl p-8 md:p-10 shadow-lg border border-slate-200 flex flex-col gap-6">
        <!-- Headers -->
        <div class="text-center mb-2">
          <h1 class="text-headline-md font-headline-md text-[#0f172a] font-bold mb-2">Selamat Datang di SiwaKen</h1>
          <p class="text-body-md font-body-md text-slate-600">Solusi Sewa Kendaraan Terpercaya</p>
        </div>
        
        <!-- Form -->
        <form class="flex flex-col gap-5" @submit.prevent="submit">
          <div class="flex flex-col gap-2">
            <label class="text-label-md font-label-md text-[#0f172a]" for="email">Email</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">mail</span>
              <input v-model="form.email" class="w-full bg-white border border-slate-300 text-[#0f172a] rounded-lg py-3 pl-10 pr-4 input-glow focus:outline-none transition-all placeholder:text-slate-400" id="email" placeholder="nama@email.com" required type="email" />
            </div>
          </div>
          
          <div class="flex flex-col gap-2">
            <label class="text-label-md font-label-md text-[#0f172a]" for="password">Password</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">lock</span>
              <input v-model="form.password" class="w-full bg-white border border-slate-300 text-[#0f172a] rounded-lg py-3 pl-10 pr-4 input-glow focus:outline-none transition-all placeholder:text-slate-400" id="password" placeholder="••••••••" required type="password" />
            </div>
          </div>
          
          <div class="flex justify-between items-center mt-2">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input v-model="form.remember" class="rounded border-slate-300 bg-white text-[#3b82f6] focus:ring-[#3b82f6]/50 focus:ring-offset-0 focus:ring-2" type="checkbox" />
              <span class="text-label-sm font-label-sm text-slate-600 group-hover:text-[#0f172a] transition-colors">Ingat saya</span>
            </label>
            <Link :href="route('password.request')" class="text-label-sm font-label-sm text-[#3b82f6] hover:text-blue-700 transition-colors">Lupa Password?</Link>
          </div>
          
          <button :disabled="form.processing" class="mt-4 bg-[#3B82F6] hover:bg-blue-600 text-white text-body-md font-body-md font-bold py-4 rounded-lg flex justify-center items-center gap-2 transition-all shadow-[0px_4px_20px_rgba(59,130,246,0.3)]" id="login-btn" type="submit">
            <span v-if="form.processing" class="material-symbols-outlined animate-spin">progress_activity</span>
            <span v-else>Masuk Ke Dashboard</span>
            <span v-if="!form.processing" class="material-symbols-outlined text-[20px]">arrow_forward</span>
          </button>
        </form>
        
        <!-- Footer Links -->
        <div class="text-center mt-4 border-t border-slate-200 pt-6">
          <p class="text-label-md font-label-md text-slate-600">
            Belum punya akun? <Link :href="route('register')" class="text-[#3b82f6] hover:text-blue-700 font-bold transition-colors">Daftar gratis</Link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<style scoped>
.input-glow:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    border-color: #3B82F6;
}
</style>
