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
      <!-- Search (TopNav Quick Filter) -->
      <div v-if="searchPlaceholder" class="relative hidden md:flex items-center">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-blue-400 transition-colors icon-18">search</span>
        <input type="text" :placeholder="searchPlaceholder"
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          @input="handleInput"
          class="pl-10 pr-8 py-1.5 bg-slate-100 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 rounded-full
                 text-label-md font-label-md text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-400
                 focus:bg-white dark:focus:bg-slate-900 focus:border-secondary dark:focus:border-blue-500
                 focus:ring-2 focus:ring-secondary/20 dark:focus:ring-blue-500/30 outline-none w-64 focus:w-80 transition-all shadow-inner dark:shadow-none" />
        <button v-if="searchQuery" @click="clearSearch"
          class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
          <span class="material-symbols-outlined icon-16">close</span>
        </button>
      </div>

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
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useDarkMode } from '@/composables/useDarkMode'
import { useSearchStore } from '@/stores/search'
import NotificationDropdown from '@/components/ui/NotificationDropdown.vue'

const { isDark, toggleTheme } = useDarkMode()
const searchStore = useSearchStore()
const searchQuery = ref(searchStore.query)

const handleSearch = () => {
  if (searchPlaceholder.value) {
    searchStore.setQuery(searchQuery.value)
  }
}

let searchTimer
const handleInput = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    handleSearch()
  }, 250)
}

const clearSearch = () => {
  searchQuery.value = ''
  searchStore.setQuery('')
}

// Keep local state in sync if store changes from elsewhere
watch(() => searchStore.query, (newVal) => {
  searchQuery.value = newVal
})

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

const searchPlaceholder = computed(() => {
  if (route.path === '/kendaraan') return 'Cari kendaraan...'
  if (route.path === '/pelanggan') return 'Cari pelanggan...'
  if (route.path === '/transaksi') return 'Cari transaksi...'
  return null // hide search on other pages
})
</script>
