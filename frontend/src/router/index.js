import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Layouts
import AdminLayout  from '@/layouts/AdminLayout.vue'
import PublicLayout from '@/layouts/PublicLayout.vue'

// Pages
import LoginPage      from '@/pages/LoginPage.vue'
import RegisterPage   from '@/pages/RegisterPage.vue'
import DashboardPage  from '@/pages/DashboardPage.vue'
import KendaraanPage  from '@/pages/KendaraanPage.vue'
import PelangganPage  from '@/pages/PelangganPage.vue'
import TransaksiPage  from '@/pages/TransaksiPage.vue'
import PembayaranPage from '@/pages/PembayaranPage.vue'
import PengembalianPage from '@/pages/PengembalianPage.vue'
import LaporanPage    from '@/pages/LaporanPage.vue'
import PengaturanPage from '@/pages/PengaturanPage.vue'
import KatalogPage         from '@/pages/public/KatalogPage.vue'
import KendaraanDetailPage from '@/pages/public/KendaraanDetailPage.vue'
import BookingSayaPage     from '@/pages/akun/BookingSayaPage.vue'
import ProfilPelangganPage from '@/pages/akun/ProfilPelangganPage.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: RegisterPage,
    meta: { guest: true },
  },
  {
    path: '/',
    component: PublicLayout,
    children: [
      { path: '',               name: 'Katalog',         component: KatalogPage },
      { path: 'kendaraan/:id',  name: 'KendaraanDetail', component: KendaraanDetailPage },
      { path: 'akun/booking',   name: 'BookingSaya',     component: BookingSayaPage,     meta: { requiresAuth: true, role: 'pelanggan' } },
      { path: 'akun/profil',    name: 'ProfilPelanggan', component: ProfilPelangganPage, meta: { requiresAuth: true, role: 'pelanggan' } },
    ],
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '',          redirect: '/admin/dashboard' },
      { path: 'dashboard', name: 'Dashboard',   component: DashboardPage   },
      { path: 'kendaraan', name: 'Kendaraan',   component: KendaraanPage   },
      { path: 'pelanggan', name: 'Pelanggan',   component: PelangganPage   },
      { path: 'transaksi', name: 'Transaksi',   component: TransaksiPage   },
      { path: 'pembayaran',name: 'Pembayaran',  component: PembayaranPage  },
      { path: 'pengembalian', name: 'Pengembalian', component: PengembalianPage },
      { path: 'laporan',   name: 'Laporan',     component: LaporanPage     },
      { path: 'pengaturan', name: 'Pengaturan', component: PengaturanPage },
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
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }
  if (to.meta.guest && auth.isLoggedIn) return auth.homePath
  // meta.role bisa berada di route induk (mis. /admin)
  const role = to.matched.map(r => r.meta.role).find(Boolean)
  if (role && auth.user?.role !== role) return auth.homePath
})

export default router
