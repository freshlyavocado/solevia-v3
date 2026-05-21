import apiClient from '@/api/axios'
import type { ApiResponse } from '@/types/api'

export default {
  async getWishlist(): Promise<ApiResponse<any[]>> {
    const response = await apiClient.get('/wishlists')
    return response.data
  },
  async toggleWishlist(productId: number): Promise<any> {
    const response = await apiClient.post('/wishlists/toggle', { product_id: productId })
    return response.data
  }
}
