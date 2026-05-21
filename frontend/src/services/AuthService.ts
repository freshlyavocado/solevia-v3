import apiClient from '@/api/axios'
import type { User } from '@/types/user'
import type { ApiResponse } from '@/types/api'

export default {
  // Fungsi untuk Login
  async login(data: any): Promise<ApiResponse<{ user: User; token: string }>> {
    const response = await apiClient.post('/login', data)
    return response.data
  },

  // Fungsi untuk Register
  async register(data: any): Promise<ApiResponse<{ user: User; token: string }>> {
    const response = await apiClient.post('/register', data)
    return response.data
  },

  // Fungsi untuk Logout
  async logout(): Promise<ApiResponse<null>> {
    const response = await apiClient.post('/logout')
    return response.data
  },

  // Fungsi untuk Mengambil Profil User saat ini
  async getProfile(): Promise<ApiResponse<User>> {
    const response = await apiClient.get('/profile')
    return response.data
  },

  // Fungsi untuk Memperbarui Profil User
  async updateProfile(data: any): Promise<ApiResponse<User>> {
    const response = await apiClient.put('/profile', data)
    return response.data
  }
}
