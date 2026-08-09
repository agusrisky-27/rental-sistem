import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Layouts
import AdminLayout from '@/layouts/AdminLayout.vue'

// Pages
import LoginPage      from '@/pages/LoginPage.vue'
import DashboardPage  from '@/pages/DashboardPage.vue'
import KendaraanPage  from '@/pages/KendaraanPage.vue'
import PelangganPage  from '@/pages/PelangganPage.vue'
import TransaksiPage  from '@/pages/TransaksiPage.vue'
import PembayaranPage from '@/pages/PembayaranPage.vue'
import PengembalianPage from '@/pages/PengembalianPage.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
    meta: { guest: true },
  },
  {
    path: '/',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '',          redirect: '/dashboard' },
      { path: 'dashboard', name: 'Dashboard',   component: DashboardPage   },
      { path: 'kendaraan', name: 'Kendaraan',   component: KendaraanPage   },
      { path: 'pelanggan', name: 'Pelanggan',   component: PelangganPage   },
      { path: 'transaksi', name: 'Transaksi',   component: TransaksiPage   },
      { path: 'pembayaran',name: 'Pembayaran',  component: PembayaranPage  },
      { path: 'pengembalian', name: 'Pengembalian', component: PengembalianPage },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach((to) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isLoggedIn) return '/login'
  if (to.meta.guest && auth.isLoggedIn) return '/dashboard'
})

export default router
