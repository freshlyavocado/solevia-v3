import apiClient from '@/api/axios'
import type { ApiResponse } from '@/types/api'
import type { Product, Brand } from '@/types/product'
import type { Category } from '@/types/category'

export default {
  async getProducts(params?: any): Promise<ApiResponse<Product[]>> {
    const response = await apiClient.get('/products', { params })
    return response.data
  },
  async getProductBySlug(slug: string): Promise<ApiResponse<Product>> {
    const response = await apiClient.get(`/products/${slug}`)
    return response.data
  },
  async getCategories(): Promise<ApiResponse<Category[]>> {
    const response = await apiClient.get('/categories')
    return response.data
  },
  async getBrands(): Promise<ApiResponse<Brand[]>> {
    const response = await apiClient.get('/brands')
    return response.data
  }
}
