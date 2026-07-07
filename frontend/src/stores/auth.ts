import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { User } from '@/types/user'
import AuthService from '@/services/AuthService'

export const useAuthStore = defineStore('auth', () => {
  // 1. STATE (Memori Ingatan): Menyimpan data yang bisa dibaca seluruh halaman
  const user = ref<User | null>(null)
  // Ambil token dari memori browser (localStorage) agar user tetap login meski halaman ditutup/refresh
  const token = ref<string | null>(localStorage.getItem('token') || null)

  // 2. ACTIONS (Fungsi Pengubah Ingatan)
  const setAuth = (userData: User, authToken: string) => {
    user.value = userData
    token.value = authToken
    // Simpan token ke localStorage sebagai "Kunci Masuk" permanen
    localStorage.setItem('token', authToken)
  }

  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  // Action khusus untuk sinkronisasi profil (misal setelah refresh halaman)
  const fetchProfile = async () => {
    if (!token.value) return
    try {
      const response = await AuthService.getProfile()
      user.value = response.data
    } catch (error) {
      // Jika token expired/invalid, bersihkan state
      clearAuth()
    }
  }

  // Action untuk memperbarui profil user
  const updateProfile = async (data: any) => {
    const response = await AuthService.updateProfile(data)
    user.value = (response as any).data || response
    return response
  }

  return {
    user,
    token,
    setAuth,
    clearAuth,
    fetchProfile,
    updateProfile
  }
})
