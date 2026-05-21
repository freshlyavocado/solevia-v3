import apiClient from '@/api/axios'
import type { ApiResponse } from '@/types/api'
import type { Order } from '@/types/order'

export default {
  async getOrders(): Promise<ApiResponse<Order[]>> {
    const response = await apiClient.get('/orders')
    return response.data
  },
  async getOrderById(id: number): Promise<ApiResponse<Order>> {
    const response = await apiClient.get(`/orders/${id}`)
    return response.data
  },
  async checkout(data: any): Promise<ApiResponse<Order>> {
    const response = await apiClient.post('/checkout', data)
    return response.data
  },
  async cancelOrder(id: number): Promise<ApiResponse<any>> {
    const response = await apiClient.put(`/orders/${id}/cancel`)
    return response.data
  }
}
