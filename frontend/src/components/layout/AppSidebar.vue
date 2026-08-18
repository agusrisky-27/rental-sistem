<template>
  <nav class="fixed left-0 top-0 h-screen w-64 flex flex-col py-6 px-4 z-40
              bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800
              transition-colors shadow-sm">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-4 mb-8">
      <img src="/logo.png" alt="SewaKen Logo" class="h-9 w-9 object-contain rounded-md shadow-sm" />
      <span class="text-headline-md font-headline-md font-bold text-slate-900 dark:text-white tracking-tight">SewaKen</span>
    </div>

    <!-- Main Nav -->
    <div class="flex-1 space-y-1.5 overflow-y-auto">
      <SidebarLink v-for="item in mainNav" :key="item.to"
        :to="item.to" :icon="item.icon" :label="item.label" />
    </div>

    <!-- Footer Nav -->
    <div class="mt-auto border-t border-slate-200 dark:border-slate-800 pt-4 space-y-2">
      <SidebarLink to="/pengaturan" icon="settings" label="Pengaturan" />

      <!-- User profile card -->
      <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700/60 mt-2 transition-colors">
        <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center
                    text-secondary dark:text-blue-300 font-bold text-label-md shrink-0">
          {{ userInitials }}
        </div>
        <div class="flex flex-col min-w-0">
          <span class="text-label-md font-medium text-slate-900 dark:text-slate-100 truncate">{{ user?.name || 'Admin' }}</span>
          <span class="text-[11px] text-slate-500 dark:text-slate-400">Administrator</span>
        </div>
      </div>

      <!-- Logout -->
      <button @click="handleLogout"
        class="w-full flex items-center gap-3 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 px-4 py-2.5
               transition-colors rounded-lg font-medium text-label-md">
        <span class="material-symbols-outlined" style="font-size:20px">logout</span>
        <span>Keluar</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SidebarLink from './SidebarLink.vue'

const auth   = useAuthStore()
const router = useRouter()
const user   = computed(() => auth.user)

const userInitials = computed(() => {
  const name = auth.user?.name || 'Admin Utama'
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

const mainNav = [
  { to: '/dashboard',    icon: 'dashboard',         label: 'Dashboard'    },
  { to: '/kendaraan',    icon: 'directions_car',    label: 'Kendaraan'    },
  { to: '/pelanggan',    icon: 'group',             label: 'Pelanggan'    },
  { to: '/transaksi',    icon: 'receipt_long',      label: 'Transaksi'    },
  { to: '/pembayaran',   icon: 'payments',          label: 'Pembayaran'   },
  { to: '/pengembalian', icon: 'assignment_return', label: 'Pengembalian' },
]

function handleLogout() {
  auth.logout()
  router.push('/login')
}
</script>
