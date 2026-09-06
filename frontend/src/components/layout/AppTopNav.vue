<template>
  <header class="fixed top-0 right-0 left-64 z-30 flex justify-between items-center
                 px-8 h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800
                 transition-colors shadow-sm">

    <!-- Page title / breadcrumb -->
    <div class="flex items-center gap-2">
      <span class="text-label-md font-label-md text-slate-500 dark:text-slate-400">SewaKen</span>
      <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 icon-16">chevron_right</span>
      <span class="text-label-md font-label-md font-semibold text-secondary dark:text-blue-400">{{ pageTitle }}</span>
    </div>

    <!-- Right actions -->
    <div class="flex items-center gap-4">
      <!-- Dark mode toggle -->
      <button @click="toggleTheme" 
        :title="isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
        class="p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-colors">
        <span class="material-symbols-outlined">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
      </button>

      <!-- Notifications -->
      <NotificationDropdown />
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useDarkMode } from '@/composables/useDarkMode'
import NotificationDropdown from '@/components/ui/NotificationDropdown.vue'

const { isDark, toggleTheme } = useDarkMode()
const route = useRoute()

const titles = {
  '/dashboard':     'Dashboard',
  '/kendaraan':     'Kendaraan',
  '/pelanggan':     'Pelanggan',
  '/transaksi':     'Transaksi',
  '/pembayaran':    'Pembayaran',
  '/pengembalian':  'Pengembalian',
  '/pengaturan':    'Pengaturan',
}

const pageTitle = computed(() => titles[route.path] || 'Dashboard')
</script>
