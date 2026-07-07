import { defineStore } from 'pinia'
import { ref } from 'vue'
import ProductService from '@/services/ProductService'
import type { Product, Brand } from '@/types/product'
import type { Category } from '@/types/category'

export const useProductStore = defineStore('product', () => {
  // 1. GLOBAL STATE (CACHING): Bertindak sebagai "Gudang Penyimpanan" sementara.
  // Daripada setiap halaman harus meminta daftar kategori/merek ke backend berulang kali,
  // kita cukup menyimpannya di sini agar website jauh lebih cepat dan irit kuota server (cukup 1x loading).
  const products = ref<Product[]>([])
  const trendingProducts = ref<Product[]>([])
  const brands = ref<Brand[]>([])
  const categories = ref<Category[]>([])
  const loading = ref(false)
  const error = ref('')

  // 2. ACTIONS: Fungsi-fungsi untuk mengambil barang dari backend dan menaruhnya ke "Gudang" di atas

  const fetchProducts = async (params?: any) => {
    loading.value = true
    try {
      const response = await ProductService.getProducts(params)
      // Mengatasi kemungkinan double "data" wrapper dari backend pagination
      const data = (response.data as any).data || response.data || []
      products.value = data
    } catch (e: any) {
      error.value = e.message
      console.error('Gagal mengambil produk:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchTrendingProducts = async () => {
    loading.value = true
    try {
      // Kita asumsikan ada parameter 'sort' atau backend mengembalikan random jika kosong
      const response = await ProductService.getProducts({ sort: 'popular', limit: 6 })
      const data = (response.data as any).data || response.data || []
      trendingProducts.value = data
    } catch (e: any) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  const fetchBrands = async () => {
    loading.value = true
    try {
      const response = await ProductService.getBrands()
      const data = (response.data as any).data || response.data || []
      brands.value = data
    } catch (e: any) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }
  
  const fetchCategories = async () => {
    loading.value = true
    try {
      const response = await ProductService.getCategories()
      const data = (response.data as any).data || response.data || []
      categories.value = data
    } catch (e: any) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  return {
    products,
    trendingProducts,
    brands,
    categories,
    loading,
    error,
    fetchProducts,
    fetchTrendingProducts,
    fetchBrands,
    fetchCategories
  }
})
