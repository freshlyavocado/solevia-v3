<!-- 
  INFO FILE
  Nama: LoginView.vue
  Fungsi: Halaman otentikasi untuk pengguna masuk (login) ke dalam akun mereka.
  Fitur: Menggunakan Vee-Validate + Zod untuk type-safe validation.
-->

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Eye, EyeOff } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import AuthService from '@/services/AuthService'
import { useForm, useField } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'

const router = useRouter()
const authStore = useAuthStore()
const showPassword = ref(false)
const apiError = ref('')

// 1. Definisikan Skema Validasi dengan Zod
const loginSchema = toTypedSchema(
  z.object({
    email: z.string().min(1, 'Email is required').email('Invalid email format'),
    password: z.string().min(1, 'Password is required'),
  })
)

// 2. Inisialisasi Form dengan Vee-Validate
const { handleSubmit, errors, isSubmitting } = useForm({
  validationSchema: loginSchema,
})

// 3. Hubungkan input ke field Vee-Validate
const { value: email } = useField<string>('email')
const { value: password } = useField<string>('password')

// 4. Tangani Submit Form
const onSubmit = handleSubmit(async (values) => {
  apiError.value = ''
  try {
    const response = await AuthService.login({ email: values.email, password: values.password })
    // @ts-ignore - response is already the data payload
    authStore.setAuth(response.user, response.token)
    router.push('/')
  } catch (error: any) {
    console.error('Login failed', error)
    apiError.value = error.response?.data?.message || 'Login failed. Please check your email and password.'
  }
})
</script>

<template>
  <div>
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-8">
      <RouterLink to="/register" class="flex-1 text-center py-3 text-sm font-bold text-gray-500 hover:text-gray-700 transition">Sign Up</RouterLink>
      <div class="flex-1 text-center py-3 text-sm font-bold border-b-2 border-black text-black">Log In</div>
    </div>

    <h2 class="text-xl font-bold mb-6">Log In</h2>

    <!-- Notifikasi Error dari API -->
    <div v-if="apiError" class="mb-4 p-3 bg-red-100 text-red-600 text-sm rounded-md border border-red-200">
      {{ apiError }}
    </div>

    <form @submit="onSubmit" class="space-y-5">
      <!-- Input Email -->
      <div>
        <input 
          v-model="email" 
          type="email" 
          placeholder="Email Address*" 
          class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-1 transition"
          :class="errors.email ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#3771C8] focus:ring-[#3771C8]'"
        />
        <p v-if="errors.email" class="text-red-500 text-xs mt-1 font-medium">{{ errors.email }}</p>
      </div>

      <!-- Input Password -->
      <div>
        <div class="relative">
          <input 
            v-model="password" 
            :type="showPassword ? 'text' : 'password'" 
            placeholder="Password*" 
            class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-1 transition pr-12"
            :class="errors.password ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#3771C8] focus:ring-[#3771C8]'"
          />
          <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
            <EyeOff v-if="!showPassword" class="h-5 w-5" />
            <Eye v-else class="h-5 w-5" />
          </button>
        </div>
        <p v-if="errors.password" class="text-red-500 text-xs mt-1 font-medium">{{ errors.password }}</p>
      </div>

      <div class="flex justify-end">
        <a href="#" class="text-xs font-bold text-gray-700 hover:text-[#3771C8] transition">Forgot Password?</a>
      </div>

      <button type="submit" :disabled="isSubmitting" class="w-full bg-[#3771C8] text-white font-bold py-3 rounded-md hover:bg-blue-700 transition disabled:opacity-70 flex justify-center items-center">
        <span v-if="isSubmitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
        {{ isSubmitting ? 'Loading...' : 'Log In' }}
      </button>
    </form>

    <div class="mt-8 relative flex items-center justify-center">
      <div class="border-t border-gray-200 w-full absolute"></div>
      <span class="bg-white px-4 text-xs text-gray-400 relative z-10">OR</span>
    </div>

    <div class="text-center mt-4 mb-6 text-xs text-gray-600">Log In with</div>
    
    <div class="flex justify-center space-x-4">
      <button class="w-10 h-10 border border-gray-300 rounded-md flex items-center justify-center hover:bg-gray-50 transition">
        <span class="font-bold text-red-500">G</span>
      </button>
      <button class="w-10 h-10 border border-gray-300 rounded-md flex items-center justify-center hover:bg-gray-50 transition">
        <span class="font-bold text-[#3771C8]">f</span>
      </button>
      <button class="w-10 h-10 border border-gray-300 rounded-md flex items-center justify-center hover:bg-gray-50 transition">
        <span class="font-bold text-black">X</span>
      </button>
    </div>

    <div class="text-center mt-8 text-xs text-gray-600">
      Need an account? <RouterLink to="/register" class="text-[#3771C8] font-bold hover:underline">Sign Up</RouterLink>
    </div>
  </div>
</template>
