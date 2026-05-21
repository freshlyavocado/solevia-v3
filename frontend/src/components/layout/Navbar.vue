<!-- 
  INFO FILE
  Nama: Navbar.vue
  Fungsi: Komponen Navigasi atas yang berisi logo, fitur pencarian, ikon keranjang, dan menu kategori.
-->

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { Search, Heart, ShoppingCart, User } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import ProductService from '@/services/ProductService'

const authStore = useAuthStore()
const cartStore = useCartStore()
const router = useRouter()
const searchQuery = ref('')

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
    showDropdown.value = false
  }
}

const searchResults = ref<any[]>([])
const showDropdown = ref(false)
let searchTimeout: any = null

watch(searchQuery, (newVal) => {
  if (!newVal.trim()) {
    searchResults.value = []
    showDropdown.value = false
    return
  }
  
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(async () => {
    try {
      const response = await ProductService.getProducts({ search: newVal })
      searchResults.value = (response.data as any[] || []).slice(0, 5)
      showDropdown.value = true
    } catch (e) {
      console.error(e)
    }
  }, 300)
})

const closeDropdown = () => {
  setTimeout(() => {
    showDropdown.value = false
  }, 200)
}

const handleLogout = async () => {
  authStore.clearAuth()
  router.push('/login')
}

// Menghitung jumlah total barang di keranjang
const cartItemsCount = computed(() => cartStore.totalItems)
</script>

<template>
  <header class="w-full bg-white sticky top-0 z-50 shadow-sm">
    <!-- Top Navbar -->
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
      <!-- Logo -->
      <RouterLink to="/" class="flex items-center">
        <img src="/images/logo.png" alt="Solevia" class="h-6.5" />
      </RouterLink>

      <!-- Search Bar -->
      <div class="hidden md:flex flex-1 max-w-2xl mx-6 relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <Search class="h-5 w-5 text-gray-400" />
        </div>
        <input
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          @focus="searchQuery.trim() && searchResults.length ? showDropdown = true : null"
          @blur="closeDropdown"
          type="text"
          class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-full leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-[#3771C8] focus:ring-1 focus:ring-[#3771C8] sm:text-sm transition duration-150 ease-in-out"
          placeholder="Search for brand, etc."
        />
        
        <!-- Search Autocomplete Dropdown -->
        <div v-if="showDropdown && searchResults.length > 0" class="absolute top-full left-0 right-0 mt-3 bg-white border border-gray-200 rounded-2xl shadow-xl z-50 overflow-hidden divide-y divide-gray-100">
          <RouterLink 
            v-for="item in searchResults" 
            :key="item.id" 
            :to="`/product/${item.slug}`"
            @click="showDropdown = false"
            class="flex items-center gap-4 p-4 hover:bg-gray-50 transition"
          >
            <div class="w-16 h-16 bg-white rounded-xl flex items-center justify-center p-2 shrink-0 border border-gray-200 shadow-sm">
               <img :src="item.images?.[0]?.image_url ? `http://localhost:8000/storage/${item.images[0].image_url}` : 'https://placehold.co/100x100?text=No+Image'" class="w-full h-full object-contain mix-blend-multiply" />
            </div>
            <div class="flex-1">
              <p class="text-xs font-bold text-[#3771C8] uppercase tracking-wider mb-0.5">{{ item.brand?.name || 'Brand' }}</p>
              <p class="text-[15px] font-semibold text-gray-900 leading-snug line-clamp-2">{{ item.name }}</p>
            </div>
          </RouterLink>
        </div>
      </div>

      <!-- Icons and About Us -->
      <div class="flex items-center gap-8 mr-8">
        <RouterLink to="/about" class="text-sm font-semibold text-gray-900 hover:text-[#3771C8] transition whitespace-nowrap">
          About Us
        </RouterLink>
        <RouterLink to="/wishlist" class="text-gray-900 hover:text-[#3771C8] transition">
          <Heart class="h-6 w-6" />
        </RouterLink>
        <RouterLink to="/cart" class="text-gray-900 hover:text-[#3771C8] transition relative">
          <ShoppingCart class="h-6 w-6" />
          <span v-if="cartItemsCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
            {{ cartItemsCount }}
          </span>
        </RouterLink>
        
        <RouterLink v-if="!authStore.token" to="/login" class="px-5 py-1.5 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
          Login
        </RouterLink>
        <div v-else class="relative group">
          <RouterLink to="/profile" class="text-gray-900 hover:text-[#3771C8] transition flex items-center">
            <User class="h-6 w-6" />
          </RouterLink>
          <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 overflow-hidden">
            <div class="p-1">
              <RouterLink to="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#3771C8] rounded-lg transition-colors font-medium">My Profile</RouterLink>
              <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium">Sign Out</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Secondary Navbar -->
    <div class="bg-[#dbeafe] w-full">
      <div class="container mx-auto px-4">
        <nav class="flex justify-center space-x-30 py-3">
          <RouterLink to="/brands" class="text-base font-bold text-gray-900 hover:text-[#3771C8] transition uppercase">Brands</RouterLink>
          <RouterLink to="/deals" class="text-base font-bold text-gray-900 hover:text-[#3771C8] transition uppercase">Deals</RouterLink>
          <RouterLink to="/men" class="text-base font-bold text-gray-900 hover:text-[#3771C8] transition uppercase">Men</RouterLink>
          <RouterLink to="/women" class="text-base font-bold text-gray-900 hover:text-[#3771C8] transition uppercase">Women</RouterLink>
          <RouterLink to="/kids" class="text-base font-bold text-gray-900 hover:text-[#3771C8] transition uppercase">Kids</RouterLink>
        </nav>
      </div>
    </div>
  </header>
</template>
