import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import CartService from '@/services/CartService'
import { useAuthStore } from './auth'

export const useCartStore = defineStore('cart', () => {
  const items = ref<any[]>([])
  const authStore = useAuthStore()

  // Menghitung total barang di keranjang
  const totalItems = computed(() => {
    return items.value.reduce((total, item) => total + item.quantity, 0)
  })

  // Mengambil data keranjang dari server
  const fetchCart = async () => {
    if (!authStore.token) return
    try {
      const response = await CartService.getCart()
      items.value = response.data.items || []
    } catch (error) {
      console.error('Gagal mengambil keranjang:', error)
    }
  }

  // Mengosongkan keranjang di memori saat logout
  const clearCart = () => {
    items.value = []
  }

  return {
    items,
    totalItems,
    fetchCart,
    clearCart
  }
})
