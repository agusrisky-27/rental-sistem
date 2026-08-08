<template>
  <div class="bg-background text-on-background font-body-md text-body-md flex min-h-screen antialiased">
    <!-- Sidebar Component -->
    <aside class="fixed left-0 top-0 h-screen w-64 flex flex-col py-6 px-4 z-40 bg-surface-container-lowest border-r border-outline-variant shadow-md">
      <div class="flex items-center gap-3 px-4 mb-10">
        <img alt="SiwaKen Logo" class="h-10 w-10 object-contain rounded-md" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBbG3aDLM3GEbSMh-xEZwgrh8Erm2Q5VRnpBmhawjZ-BceFtPUKWno0hfw7-lkD-ocwaKGDkJ9p_z6ILDnmbFWHDD83RhTlDNCtRiY4nyXt7hXkUSlU7cN379221am-mYfo1XNbRDuoRPfe7E4a_hZsEhMJqBuInajU9rMwPSkciGFWUf-lLQ5hzrXAqv72gfRaBwszYZL1Jzea14OvUnVXiAgE7lC3Ecix2oFY0pEpdF186qaERZKt">
        <span class="text-headline-md font-headline-md font-bold text-primary">SiwaKen</span>
      </div>

      <nav class="flex-1 space-y-2">
        <Link href="/dashboard" :class="[isActive('/dashboard') ? 'bg-secondary-fixed text-secondary nav-item-active' : 'text-primary hover:text-secondary hover:bg-surface-container-low', 'flex items-center gap-3 px-4 py-3 transition-all rounded-lg']">
          <span class="material-symbols-outlined" :data-weight="isActive('/dashboard') ? 'fill' : ''" :style="isActive('/dashboard') ? 'font-variation-settings: \'FILL\' 1;' : ''">dashboard</span>
          <span class="text-label-md font-label-md">Dashboard</span>
        </Link>
        <Link href="/kendaraan" :class="[isActive('/kendaraan') ? 'bg-secondary-fixed text-secondary nav-item-active' : 'text-primary hover:text-secondary hover:bg-surface-container-low', 'flex items-center gap-3 px-4 py-3 transition-all rounded-lg']">
          <span class="material-symbols-outlined" :data-weight="isActive('/kendaraan') ? 'fill' : ''" :style="isActive('/kendaraan') ? 'font-variation-settings: \'FILL\' 1;' : ''">directions_car</span>
          <span class="text-label-md font-label-md">Kendaraan</span>
        </Link>
        <Link href="/pelanggan" :class="[isActive('/pelanggan') ? 'bg-secondary-fixed text-secondary nav-item-active' : 'text-primary hover:text-secondary hover:bg-surface-container-low', 'flex items-center gap-3 px-4 py-3 transition-all rounded-lg']">
          <span class="material-symbols-outlined" :data-weight="isActive('/pelanggan') ? 'fill' : ''" :style="isActive('/pelanggan') ? 'font-variation-settings: \'FILL\' 1;' : ''">group</span>
          <span class="text-label-md font-label-md">Pelanggan</span>
        </Link>
        <Link href="/transaksi" :class="[isActive('/transaksi') ? 'bg-secondary-fixed text-secondary nav-item-active' : 'text-primary hover:text-secondary hover:bg-surface-container-low', 'flex items-center gap-3 px-4 py-3 transition-all rounded-lg']">
          <span class="material-symbols-outlined" :data-weight="isActive('/transaksi') ? 'fill' : ''" :style="isActive('/transaksi') ? 'font-variation-settings: \'FILL\' 1;' : ''">receipt_long</span>
          <span class="text-label-md font-label-md">Transaksi</span>
        </Link>
        <Link href="/pembayaran" :class="[isActive('/pembayaran') ? 'bg-secondary-fixed text-secondary nav-item-active' : 'text-primary hover:text-secondary hover:bg-surface-container-low', 'flex items-center gap-3 px-4 py-3 transition-all rounded-lg']">
          <span class="material-symbols-outlined" :data-weight="isActive('/pembayaran') ? 'fill' : ''" :style="isActive('/pembayaran') ? 'font-variation-settings: \'FILL\' 1;' : ''">payments</span>
          <span class="text-label-md font-label-md">Pembayaran</span>
        </Link>
        <Link href="/pengembalian" :class="[isActive('/pengembalian') ? 'bg-secondary-fixed text-secondary nav-item-active' : 'text-primary hover:text-secondary hover:bg-surface-container-low', 'flex items-center gap-3 px-4 py-3 transition-all rounded-lg']">
          <span class="material-symbols-outlined" :data-weight="isActive('/pengembalian') ? 'fill' : ''" :style="isActive('/pengembalian') ? 'font-variation-settings: \'FILL\' 1;' : ''">assignment_return</span>
          <span class="text-label-md font-label-md">Pengembalian</span>
        </Link>
      </nav>
      
      <div class="mt-auto space-y-2 pt-4 border-t border-outline-variant/20">
        <Link href="/pengaturan" class="flex items-center gap-3 text-primary hover:text-secondary px-4 py-3 transition-all hover:bg-surface-container-low rounded-lg">
          <span class="material-symbols-outlined">settings</span>
          <span class="text-label-md font-label-md">Pengaturan</span>
        </Link>
        <div class="flex items-center gap-3 px-4 py-3 mt-2">
          <img class="w-10 h-10 rounded-full object-cover border-2 border-outline-variant" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWr2ZdB6GWd3NzaEEpJxZuuYN5brySYo8XnXn_vSvij6aodFcECUY2SIeLuwWAJ9QtEqbKPvjIm61ak0iCA4dWzWXxhPzJ6es7965NJ2pMZRybafZwvGKenkUBXi2mX3cRL4vTcA_9rz1Ax3cieWygjJaQdU-dfelAc0LcDGuXF3yeel73xWEN5-xOJxZRRvvLyEh3OYgzFH8nNNphi5q6J53OEFofM5ioRafCpRF7vGjb75qr5NUa">
          <div class="flex flex-col">
            <span class="text-label-md font-label-md text-primary">Admin Utama</span>
            <span class="text-label-sm font-label-sm text-on-surface-variant">Administrator</span>
          </div>
        </div>
        <Link href="/logout" method="post" as="button" class="flex items-center gap-3 text-error/90 hover:text-error px-4 py-3 transition-all hover:bg-error-container/10 rounded-lg mt-2 w-full text-left">
          <span class="material-symbols-outlined">logout</span>
          <span class="text-label-md font-label-md">Keluar</span>
        </Link>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 ml-64 flex flex-col min-h-screen relative bg-surface-container-low">
      <!-- Top Nav Component -->
      <header class="fixed top-0 right-0 left-64 z-30 flex justify-between items-center px-8 h-16 bg-surface border-b border-outline-variant">
        <div class="flex items-center gap-4">
          <h2 class="text-headline-sm font-headline-md font-bold text-primary">{{ pageTitle }}</h2>
        </div>
        <div class="flex items-center gap-6">
          <div v-if="showSearch" class="relative hidden md:block">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input class="pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-full text-label-md font-label-md focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none w-64 transition-all" placeholder="Cari..." type="text">
          </div>
          <div class="flex items-center gap-4">
            <button class="p-2 text-on-surface-variant hover:bg-surface-container-low rounded-full transition-colors relative">
              <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
              <span class="absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
            </button>
            <button class="p-2 text-on-surface-variant hover:bg-surface-container-low rounded-full transition-colors">
              <span class="material-symbols-outlined" data-icon="chat_bubble">chat_bubble</span>
            </button>
            <button class="p-2 text-on-surface-variant hover:bg-surface-container-low rounded-full transition-colors">
              <span class="material-symbols-outlined" data-icon="account_circle">account_circle</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Slot Content -->
      <div class="pt-24 px-8 pb-12 flex-1 overflow-y-auto">
        <slot></slot>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  pageTitle: {
    type: String,
    default: 'SiwaKen Dashboard'
  },
  showSearch: {
    type: Boolean,
    default: true
  }
});

const page = usePage();
const isActive = (path) => {
  // Simple check for active route
  return page.url.startsWith(path);
};
</script>

<style scoped>
.nav-item-active {
  position: relative;
}
.nav-item-active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  height: 60%;
  width: 4px;
  background-color: #0058be;
  border-radius: 0 4px 4px 0;
}
</style>
