<template>
  <header class="fixed top-0 right-0 left-64 z-30 flex justify-between items-center
                 px-8 h-16 bg-surface border-b border-outline-variant">

    <!-- Page title / breadcrumb -->
    <div class="flex items-center gap-2">
      <span class="text-label-md font-label-md text-on-surface-variant">SewaKen</span>
      <span class="material-symbols-outlined text-on-surface-variant" style="font-size:16px">chevron_right</span>
      <span class="text-label-md font-label-md font-semibold text-secondary">{{ pageTitle }}</span>
    </div>

    <!-- Right actions -->
    <div class="flex items-center gap-4">
      <!-- Search -->
      <div v-if="searchPlaceholder" class="relative hidden md:block">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline"
          style="font-size:18px">search</span>
        <input type="text" :placeholder="searchPlaceholder"
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          class="pl-10 pr-4 py-2 bg-surface-container-low dark:bg-gray-800 border-none rounded-full
                 text-label-md font-label-md focus:ring-2 focus:ring-secondary/20 outline-none w-56 transition-all" />
      </div>

      <!-- Dark mode toggle -->
      <button @click="toggleTheme" class="p-2 rounded-full text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-gray-800 transition-colors">
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
