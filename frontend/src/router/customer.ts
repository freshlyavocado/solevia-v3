import type { RouteRecordRaw } from 'vue-router'

// Menggunakan DefaultLayout dan AuthLayout yang sudah kita buat di Fase 4
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

export const customerRoutes: RouteRecordRaw[] = [
  {
    path: '/',
    component: DefaultLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('@/pages/HomeView.vue')
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/pages/ProfileView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'cart',
        name: 'cart',
        component: () => import('@/pages/CartView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'checkout',
        name: 'checkout',
        component: () => import('@/pages/CheckoutView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'payment/:id',
        name: 'payment',
        component: () => import('@/pages/PaymentView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'orders/:id',
        name: 'order-detail',
        component: () => import('@/pages/OrderDetailView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'wishlist',
        name: 'wishlist',
        component: () => import('@/pages/WishlistView.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'product/:slug',
        name: 'product-detail',
        component: () => import('@/pages/ProductDetailView.vue')
      },
      {
        path: 'brands',
        name: 'brands',
        component: () => import('@/pages/BrandsView.vue')
      },
      {
        path: 'brands/:id',
        name: 'brand-detail',
        component: () => import('@/pages/BrandDetailView.vue')
      },
      {
        path: 'deals',
        name: 'deals',
        component: () => import('@/pages/DealsView.vue')
      },
      {
        path: 'search',
        name: 'search',
        component: () => import('@/pages/SearchView.vue')
      },
      {
        path: 'category/:slug',
        name: 'category',
        component: () => import('@/pages/CategoryView.vue')
      },
      {
        path: 'men',
        name: 'category-men',
        component: () => import('@/pages/CategoryView.vue')
      },
      {
        path: 'women',
        name: 'category-women',
        component: () => import('@/pages/CategoryView.vue')
      },
      {
        path: 'kids',
        name: 'category-kids',
        component: () => import('@/pages/CategoryView.vue')
      },
      {
        path: 'about',
        name: 'about',
        component: () => import('@/pages/AboutUsView.vue')
      },
      {
        path: 'contact',
        name: 'contact',
        component: () => import('@/pages/ContactUsView.vue')
      },
      {
        path: 'size-guide',
        name: 'size-guide',
        component: () => import('@/pages/SizeGuideView.vue')
      }
    ]
  },
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/LoginView.vue'),
        meta: { guestOnly: true }
      },
      {
        path: '/register',
        name: 'register',
        component: () => import('@/pages/RegisterView.vue'),
        meta: { guestOnly: true }
      }
    ]
  }
]
