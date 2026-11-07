<template>
  <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors">
    <!-- Header -->
    <header class="sticky top-0 z-30 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm transition-colors">
      <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-3">
        <RouterLink to="/" class="flex items-center gap-2 shrink-0">
          <img src="/logo.png" alt="SewaKen Logo" class="h-8 w-8 object-contain rounded-md" />
          <span class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white tracking-tight">SewaKen</span>
        </RouterLink>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-1">
          <RouterLink v-for="l in links" :key="l.to" :to="l.to" :class="linkClass">{{ l.label }}</RouterLink>
          <button v-if="auth.isLoggedIn && !auth.isAdmin" @click="handleLogout" :class="linkClass + ' text-rose-600 dark:text-rose-400'">Keluar</button>
        </nav>

        <div class="flex items-center gap-1">
          <button @click="toggleTheme" :title="isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
            class="p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <span class="material-symbols-outlined">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
          </button>
          <NotificationDropdown v-if="auth.isLoggedIn && !auth.isAdmin" />
          <button @click="menuOpen = !menuOpen" class="md:hidden p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Menu">
            <span class="material-symbols-outlined">{{ menuOpen ? 'close' : 'menu' }}</span>
          </button>
        </div>
      </div>

      <!-- Mobile nav -->
      <nav v-if="menuOpen" class="md:hidden border-t border-slate-200 dark:border-slate-800 px-4 py-2 flex flex-col" @click="menuOpen = false">
        <RouterLink v-for="l in links" :key="l.to" :to="l.to" :class="linkClass">{{ l.label }}</RouterLink>
        <button v-if="auth.isLoggedIn && !auth.isAdmin" @click="handleLogout" :class="linkClass + ' text-left text-rose-600 dark:text-rose-400'">Keluar</button>
      </nav>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto px-4 py-8">
      <RouterView />
    </main>

    <footer class="border-t border-slate-200 dark:border-slate-800 py-6 text-center text-label-md text-slate-500 dark:text-slate-400">
      &copy; {{ new Date().getFullYear() }} SewaKen. Sewa kendaraan per hari.
    </footer>

    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDarkMode } from '@/composables/useDarkMode'
import NotificationDropdown from '@/components/ui/NotificationDropdown.vue'
import ToastContainer from '@/components/ui/ToastContainer.vue'

const auth   = useAuthStore()
const router = useRouter()
const { isDark, toggleTheme } = useDarkMode()
const menuOpen = ref(false)

const linkClass = 'px-3 py-2 rounded-lg text-label-md font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors'

const links = computed(() => {
  const list = [{ to: '/', label: 'Katalog' }]
  if (!auth.isLoggedIn) {
    list.push({ to: '/login', label: 'Masuk' }, { to: '/register', label: 'Daftar' })
  } else if (auth.isAdmin) {
    list.push({ to: '/admin/dashboard', label: 'Dashboard Admin' })
  } else {
    list.push({ to: '/akun/booking', label: 'Booking Saya' }, { to: '/akun/profil', label: 'Profil' })
  }
  return list
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
