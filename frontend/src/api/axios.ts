import axios from 'axios'

// Membuat instance axios dengan URL dasar dari file .env
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request Interceptor: Otomatis menyisipkan token Bearer jika ada di localStorage
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response Interceptor: Menangani error secara global
apiClient.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Cek apakah error dari backend adalah 401 Unauthorized (Token Expired / Tidak Valid)
    if (error.response && error.response.status === 401) {
      // 1. Hapus token lama yang sudah tidak berguna
      localStorage.removeItem('token')
      
      // 2. Redirect/Tendang user secara paksa ke halaman login
      // Kita cegah infinite loop jika user sudah berada di halaman login
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    
    // Nanti logika "Toast Notification" untuk pesan error bisa diletakkan di sini
    
    return Promise.reject(error)
  }
)

export default apiClient
