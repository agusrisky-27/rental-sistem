<template>
  <nav class="fixed left-0 top-0 h-screen w-64 flex flex-col py-6 px-4 z-40
              bg-surface-container-lowest border-r border-outline-variant shadow-md">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-4 mb-10">
      <div class="h-10 w-10 rounded-md bg-secondary flex items-center justify-center">
        <span class="material-symbols-outlined text-white fill">directions_car</span>
      </div>
      <span class="text-headline-md font-headline-md font-bold text-primary">SiwaKen</span>
    </div>

    <!-- Main Nav -->
    <div class="flex-1 space-y-1">
      <SidebarLink v-for="item in mainNav" :key="item.to"
        :to="item.to" :icon="item.icon" :label="item.label" />
    </div>

    <!-- Footer Nav -->
    <div class="mt-auto border-t border-outline-variant/20 pt-4 space-y-1">
      <SidebarLink to="/pengaturan" icon="settings" label="Pengaturan" />

      <!-- User profile -->
      <div class="flex items-center gap-3 px-4 py-3 mt-2">
        <div class="w-10 h-10 rounded-full bg-secondary-fixed flex items-center justify-center
                    text-secondary font-bold text-label-md">
          {{ userInitials }}
        </div>
        <div class="flex flex-col">
          <span class="text-label-md font-label-md text-primary">{{ user?.name || 'Admin' }}</span>
          <span class="text-label-sm font-label-sm text-on-surface-variant">Administrator</span>
        </div>
      </div>

      <!-- Logout -->
      <button @click="handleLogout"
        class="w-full flex items-center gap-3 text-error/90 hover:text-error px-4 py-3
               transition-all hover:bg-error-container/10 dark:hover:bg-red-900/30 rounded-lg mt-2">
        <span class="material-symbols-outlined">logout</span>
        <span class="text-label-md font-label-md">Keluar</span>
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
