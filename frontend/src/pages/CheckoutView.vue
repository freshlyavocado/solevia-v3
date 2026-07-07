<!-- 
  INFO FILE
  Nama: CheckoutView.vue
  Fungsi: Halaman form pengiriman dan ringkasan pesanan sebelum pembayaran.
-->
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import OrderService from '@/services/OrderService'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

// Form Data
const firstName = ref('')
const lastName = ref('')
const phoneNumber = ref('')
const address = ref('')
const country = ref('')
const province = ref('')
const city = ref('')
const subdistrict = ref('')
const postalCode = ref('')

const isSubmitting = ref(false)

onMounted(async () => {
  if (!authStore.token) {
    router.push('/login')
    return
  }
  
  // Memastikan data profil pengguna sudah diambil dari backend (berguna jika user baru saja me-refresh halaman)
  if (!authStore.user) {
    await authStore.fetchProfile()
  }
  
  // Auto-fill form data from user profile
  if (authStore.user) {
    const nameParts = authStore.user.name.split(' ')
    firstName.value = nameParts[0] || ''
    lastName.value = nameParts.slice(1).join(' ') || ''
  }

  cartStore.fetchCart()
})

const subtotal = computed(() => {
  if (!cartStore.items) return 0
  return cartStore.items.reduce((total, item) => {
    const price = item.product?.discount_price || item.product?.price || 0
    return total + (price * item.quantity)
  }, 0)
})

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)
}

const handlePayment = async () => {
  if (!firstName.value || !lastName.value || !phoneNumber.value || !address.value || !province.value || !city.value || !postalCode.value) {
    alert('Please fill all required fields marked with *.')
    return
  }
  
  isSubmitting.value = true
  try {
    const fullAddress = subdistrict.value ? `${address.value}, Kec. ${subdistrict.value}` : address.value
    
    const payload = {
      recipient_name: `${firstName.value} ${lastName.value}`,
      phone_number: phoneNumber.value,
      address: fullAddress,
      city: city.value,
      province: province.value,
      postal_code: postalCode.value,
      payment_method: 'xendit'
    }
    
    const response = await OrderService.checkout(payload)
    
    // Keranjang berhasil dicheckout, redirect ke halaman payment
    cartStore.clearCart() // Reset cart di sisi frontend
    
    // Cek struktur response (tergantung backend mengembalikan data atau data.data)
    const orderId = (response as any).id || (response as any).data?.id || (response as any).order?.id
    if (orderId) {
      router.push(`/payment/${orderId}`)
    } else {
      router.push('/profile')
    }
  } catch (error: any) {
    console.error('Checkout error', error)
    alert(error.response?.data?.message || 'Failed to process checkout. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    
    <!-- Progress Bar -->
    <div class="flex justify-center items-center mb-12 mt-4">
      <div class="flex flex-col items-center relative">
        <div class="w-10 h-10 rounded-full bg-[#3771C8] text-white flex items-center justify-center font-bold text-sm z-10 ring-4 ring-white">1</div>
        <div class="text-[#3771C8] font-bold text-xs absolute top-12 mt-1">Delivery</div>
      </div>
      <div class="h-1 w-32 md:w-64 bg-[#3771C8] -mx-1 z-0"></div>
      <div class="h-1 w-32 md:w-64 bg-gray-200 -mx-1 z-0"></div>
      <div class="flex flex-col items-center relative">
        <div class="w-10 h-10 rounded-full bg-gray-300 text-white flex items-center justify-center font-bold text-sm z-10 ring-4 ring-white">2</div>
        <div class="text-gray-400 font-bold text-xs absolute top-12 mt-1">payment</div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Forms -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Contact Information Block -->
        <div class="bg-white border border-gray-300 rounded-2xl p-6 md:p-8">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Contact Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-500">*</span></label>
              <input v-model="firstName" type="text" placeholder="your first name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last name <span class="text-red-500">*</span></label>
              <input v-model="lastName" type="text" placeholder="your last name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone number <span class="text-red-500">*</span></label>
              <input v-model="phoneNumber" type="tel" placeholder="your phone number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
              <input v-model="address" type="text" placeholder="your address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
              <input v-model="country" type="text" placeholder="your country" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Province <span class="text-red-500">*</span></label>
              <input v-model="province" type="text" placeholder="your province" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
              <input v-model="city" type="text" placeholder="your city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Subdistrict</label>
              <input v-model="subdistrict" type="text" placeholder="your subdistrict" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Postal code <span class="text-red-500">*</span></label>
              <input v-model="postalCode" type="text" placeholder="your postal code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" required />
            </div>
          </div>
        </div>

        <!-- Shipping Information Block -->
        <div class="bg-white border border-gray-300 rounded-2xl p-6 md:p-8">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Shipping Information</h2>
          <div class="border border-gray-300 rounded-xl p-4 flex items-center">
            <input type="radio" checked class="w-4 h-4 text-[#3771C8] focus:ring-[#3771C8] border-gray-300" />
            <div class="ml-3 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
              </svg>
              <span class="font-medium text-gray-900">Regular Delivery</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Summary -->
      <div class="lg:col-span-1">
        <div class="bg-white border border-gray-300 rounded-2xl p-6 md:p-8 sticky top-24">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Shopping Summary</h2>
          
          <div v-if="cartStore.items && cartStore.items.length > 0" class="max-h-64 overflow-y-auto pr-2 mb-6 space-y-4">
            <div v-for="item in cartStore.items" :key="item.id" class="flex gap-4 p-3 border border-gray-100 rounded-xl bg-gray-50/50">
              <div class="w-20 h-20 bg-white rounded-lg flex-shrink-0 flex items-center justify-center p-2 border border-gray-100">
                <img 
                  :src="item.product?.images?.[0]?.image_url ? `http://localhost:8000/storage/${item.product.images[0].image_url}` : 'https://placehold.co/100x100?text=No+Image'" 
                  :alt="item.product?.name" 
                  class="w-full h-full object-contain mix-blend-multiply"
                />
              </div>
              <div class="flex-1">
                <p class="text-xs font-bold text-[#3771C8] uppercase mb-0.5">{{ item.product?.brand?.name || 'Brand' }}</p>
                <h3 class="text-sm font-bold text-gray-900 leading-tight mb-2">{{ item.product?.name || 'Unknown Product' }}</h3>
                <p class="text-xs text-gray-500">Quantity: {{ item.quantity }}</p>
                <p class="text-xs text-gray-500">Size: {{ item.variant?.size || 'Default' }}</p>
              </div>
            </div>
          </div>
          
          <div class="border-t border-gray-200 pt-6 mb-6">
            <div class="flex justify-between text-gray-600 font-medium mb-3">
              <span>Subtotal</span>
              <span>{{ formatPrice(subtotal) }}</span>
            </div>
            <div class="flex justify-between text-gray-600 font-medium">
              <span>Delivery</span>
              <span class="text-gray-900">Free</span>
            </div>
          </div>
          
          <div class="border-t border-gray-200 pt-4 mb-8">
            <div class="flex justify-between items-center">
              <span class="font-bold text-gray-900">Order Amount</span>
              <span class="font-extrabold text-xl text-gray-900">{{ formatPrice(subtotal) }}</span>
            </div>
          </div>
          
          <button 
            @click="handlePayment" 
            :disabled="isSubmitting"
            class="w-full bg-[#3771C8] text-white font-bold py-4 rounded-xl hover:opacity-90 flex items-center justify-center"
          >
            <div v-if="isSubmitting" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            Continue to Payment
          </button>
        </div>
      </div>
      
    </div>
  </div>
</template>
