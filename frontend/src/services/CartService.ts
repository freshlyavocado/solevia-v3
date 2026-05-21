import apiClient from '@/api/axios'
import type { ApiResponse } from '@/types/api'
import type { Cart } from '@/types/cart'

export default {
  async getCart(): Promise<ApiResponse<Cart>> {
    const response = await apiClient.get('/cart')
    return response.data
  },
  async addItem(data: { variant_id: number; quantity: number }): Promise<ApiResponse<any>> {
    const response = await apiClient.post('/cart/items', data)
    return response.data
  },
  async updateItem(itemId: number, data: { quantity: number }): Promise<ApiResponse<any>> {
    const response = await apiClient.put(`/cart/items/${itemId}`, data)
    return response.data
  },
  async removeItem(itemId: number): Promise<ApiResponse<any>> {
    const response = await apiClient.delete(`/cart/items/${itemId}`)
    return response.data
  }
}
