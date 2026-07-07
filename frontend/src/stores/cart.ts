import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import CartService from '@/services/CartService'
import { useAuthStore } from './auth'

export const useCartStore = defineStore('cart', () => {
  const items = ref<any[]>([])
  const loading = ref(false)
  const authStore = useAuthStore()

  // 1. COMPUTED (Otak Pintar): Menghitung otomatis total semua barang
  // Digunakan untuk menampilkan notifikasi angka merah di ikon keranjang Navbar
  const totalItems = computed(() => {
    return items.value.reduce((total, item) => total + item.quantity, 0)
  })

  // Mengambil data keranjang dari server
  const fetchCart = async () => {
    if (!authStore.token) return
    loading.value = true
    try {
      const response = await CartService.getCart()
      items.value = response.data.items || []
    } catch (error) {
      console.error('Gagal mengambil keranjang:', error)
    } finally {
      loading.value = false
    }
  }

  // Memperbarui kuantitas barang
  const updateItem = async (itemId: number, quantity: number) => {
    loading.value = true
    try {
      await CartService.updateItem(itemId, { quantity })
      await fetchCart() // Refresh data setelah update
    } catch (error) {
      console.error('Gagal update item:', error)
    } finally {
      loading.value = false
    }
  }

  // Menghapus barang dari keranjang
  const removeItem = async (itemId: number) => {
    loading.value = true
    try {
      await CartService.removeItem(itemId)
      await fetchCart() // Refresh data setelah hapus
    } catch (error) {
      console.error('Gagal hapus item:', error)
    } finally {
      loading.value = false
    }
  }

  // Mengosongkan keranjang di memori saat logout
  const clearCart = () => {
    items.value = []
  }

  return {
    items,
    loading,
    totalItems,
    fetchCart,
    updateItem,
    removeItem,
    clearCart
  }
})
