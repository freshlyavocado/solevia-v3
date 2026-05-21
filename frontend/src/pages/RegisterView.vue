<!-- 
  INFO FILE
  Nama: RegisterView.vue
  Fungsi: Halaman otentikasi untuk pengguna baru mendaftar (register) akun.
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
const showConfirmPassword = ref(false)
const apiError = ref('')

// 1. Definisikan Skema Validasi dengan Zod
const registerSchema = toTypedSchema(
  z.object({
    firstName: z.string().min(2, 'First name must be at least 2 characters'),
    lastName: z.string().min(2, 'Last name must be at least 2 characters'),
    email: z.string().min(1, 'Email is required').email('Invalid email format'),
    password: z.string()
      .min(8, 'Password must be at least 8 characters')
      .regex(/[A-Z]/, 'Must contain an uppercase letter')
      .regex(/[0-9]/, 'Must contain a number')
      .regex(/[^A-Za-z0-9]/, 'Must contain a symbol'),
    confirmPassword: z.string()
  }).refine((data) => data.password === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"],
  })
)

// 2. Inisialisasi Form
const { handleSubmit, errors, isSubmitting } = useForm({
  validationSchema: registerSchema,
})

// 3. Hubungkan input ke field
const { value: firstName } = useField<string>('firstName')
const { value: lastName } = useField<string>('lastName')
const { value: email } = useField<string>('email')
const { value: password } = useField<string>('password')
const { value: confirmPassword } = useField<string>('confirmPassword')

// 4. Tangani Submit Form
const onSubmit = handleSubmit(async (values) => {
  apiError.value = ''
  try {
    const response = await AuthService.register({
      name: `${values.firstName} ${values.lastName}`,
      email: values.email,
      password: values.password,
      password_confirmation: values.confirmPassword
    })
    // @ts-ignore - response is already the data payload
    authStore.setAuth(response.user, response.token)
    alert('Sign up successful!')
    router.push('/')
  } catch (error: any) {
    console.error("Signup error:", error)
    apiError.value = error.response?.data?.message || 'Sign up failed. Please try a different email.'
  }
})
</script>

<template>
  <div>
    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-8">
      <div class="flex-1 text-center py-3 text-sm font-bold border-b-2 border-black text-black">Sign Up</div>
      <RouterLink to="/login" class="flex-1 text-center py-3 text-sm font-bold text-gray-500 hover:text-gray-700 transition">Log In</RouterLink>
    </div>

    <h2 class="text-xl font-bold mb-6">Sign Up</h2>

    <div v-if="apiError" class="mb-4 p-3 bg-red-100 text-red-600 text-sm rounded-md border border-red-200">
      {{ apiError }}
    </div>

    <form @submit="onSubmit" class="space-y-4">
      <div>
        <input 
          v-model="firstName" 
          type="text" 
          placeholder="First Name*" 
          class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-1 transition"
          :class="errors.firstName ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#3771C8] focus:ring-[#3771C8]'"
        />
        <p v-if="errors.firstName" class="text-red-500 text-xs mt-1">{{ errors.firstName }}</p>
      </div>

      <div>
        <input 
          v-model="lastName" 
          type="text" 
          placeholder="Last Name*" 
          class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-1 transition"
          :class="errors.lastName ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#3771C8] focus:ring-[#3771C8]'"
        />
        <p v-if="errors.lastName" class="text-red-500 text-xs mt-1">{{ errors.lastName }}</p>
      </div>

      <div>
        <input 
          v-model="email" 
          type="email" 
          placeholder="Email Address*" 
          class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-1 transition"
          :class="errors.email ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#3771C8] focus:ring-[#3771C8]'"
        />
        <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
      </div>

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
        <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
        <p v-else class="text-[10px] font-bold text-gray-500 mt-1">At least 8 characters, 1 uppercase letter, 1 number & 1 symbol</p>
      </div>

      <div>
        <div class="relative">
          <input 
            v-model="confirmPassword" 
            :type="showConfirmPassword ? 'text' : 'password'" 
            placeholder="Confirm Password*" 
            class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-1 transition pr-12"
            :class="errors.confirmPassword ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-[#3771C8] focus:ring-[#3771C8]'"
          />
          <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
            <EyeOff v-if="!showConfirmPassword" class="h-5 w-5" />
            <Eye v-else class="h-5 w-5" />
          </button>
        </div>
        <p v-if="errors.confirmPassword" class="text-red-500 text-xs mt-1">{{ errors.confirmPassword }}</p>
      </div>

      <button type="submit" :disabled="isSubmitting" class="w-full bg-[#3771C8] text-white font-bold py-3 rounded-md hover:bg-blue-700 transition mt-6 disabled:opacity-70 flex items-center justify-center">
        <span v-if="isSubmitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></span>
        {{ isSubmitting ? 'Loading...' : 'Sign Up' }}
      </button>
    </form>

    <div class="mt-8 relative flex items-center justify-center">
      <div class="border-t border-gray-200 w-full absolute"></div>
      <span class="bg-white px-4 text-xs text-gray-400 relative z-10">OR</span>
    </div>

    <div class="text-center mt-4 mb-6 text-xs text-gray-600">Sign up with</div>
    
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
      Already have an account? <RouterLink to="/login" class="text-[#3771C8] font-bold hover:underline">Log In</RouterLink>
    </div>
  </div>
</template>
