<template>
  <header class="fixed top-0 right-0 left-64 z-30 flex justify-between items-center
                 px-8 h-16 bg-surface border-b border-outline-variant">

    <!-- Page title / breadcrumb -->
    <div class="flex items-center gap-2">
      <span class="text-label-md font-label-md text-on-surface-variant">SiwaKen</span>
      <span class="material-symbols-outlined text-on-surface-variant" style="font-size:16px">chevron_right</span>
      <span class="text-label-md font-label-md font-semibold text-secondary">{{ pageTitle }}</span>
    </div>

    <!-- Right actions -->
    <div class="flex items-center gap-4">
      <!-- Search -->
      <div class="relative hidden md:block">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline"
          style="font-size:18px">search</span>
        <input type="text" placeholder="Cari..."
          class="pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-full
                 text-label-md font-label-md focus:ring-2 focus:ring-secondary/20 outline-none w-56 transition-all" />
      </div>

      <!-- Dark mode toggle -->
      <button class="p-2 rounded-full text-on-surface-variant hover:bg-surface-container-low transition-colors">
        <span class="material-symbols-outlined">dark_mode</span>
      </button>

      <!-- Notifications -->
      <button class="p-2 rounded-full text-on-surface-variant hover:bg-surface-container-low transition-colors relative">
        <span class="material-symbols-outlined">notifications</span>
        <span class="absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
      </button>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

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
