import { createRouter, createWebHistory } from 'vue-router'
import { customerRoutes } from './customer'
import { useAuthStore } from '@/stores/auth'

// Menggabungkan semua rute menjadi satu
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    ...customerRoutes,
    // Route fallback jika URL tidak ditemukan (Error 404)
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/pages/NotFoundView.vue')
    }
  ]
})

// ROUTE GUARD (Auth Middleware)
// Fungsi ini dieksekusi setiap kali user berpindah halaman
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = !!authStore.token
  
  // A. Cek jika halaman membutuhkan Login (requiresAuth)
  // Jika butuh login tapi belum punya token, tendang ke halaman login
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'login' })
  }

  // B. Cek jika halaman HANYA untuk tamu (guestOnly), contoh: Halaman Login/Register
  // Jika user sudah login (punya token) tapi iseng buka halaman /login, tendang kembali ke Home
  if (to.meta.guestOnly && isAuthenticated) {
    return next({ name: 'home' })
  }

  // Jika semua pengecekan aman, izinkan user masuk ke halaman yang dituju
  next()
})

export default router
