<!-- 
  INFO FILE
  Nama: PaymentView.vue
  Fungsi: Halaman untuk memilih metode pembayaran dan memproses gateway pembayaran (Midtrans/Xendit).
-->
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import { CreditCard, QrCode } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const orderId = route.params.id as string
const order = ref<any>(null)
const loading = ref(true)
const isProcessing = ref(false)

const paymentMethod = ref('qris')
const promoCode = ref('')

onMounted(async () => {
  if (!authStore.token) {
    router.push('/login')
    return
  }

  try {
    const { data } = await api.get(`/orders/${orderId}`)
    order.value = data.data || data
  } catch (error) {
    console.error('Failed to fetch order', error)
    alert('Order not found.')
    router.push('/profile')
  } finally {
    loading.value = false
  }
})

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)
}

const processPayment = async () => {
  isProcessing.value = true
  try {
    // Memanggil endpoint Xendit untuk mendapatkan link Invoice URL
    const { data } = await api.post(`/orders/${orderId}/pay`, { method: paymentMethod.value })
    
    if (data.invoice_url) {
      // Jika berhasil, alihkan browser ke halaman Xendit
      if (data.message) {
         // Pesan bahwa ini fallback jika API Key Secret tidak diset
         console.warn(data.message);
         alert('Xendit Invoice Generated! Redirecting to payment page...');
      }
      window.location.href = data.invoice_url
    } else {
      alert('Payment successful! Your order is now being processed.')
      router.push('/profile')
    }
  } catch (error: any) {
    console.error('Payment error', error)
    alert(error.response?.data?.message || 'Payment failed. Please try again.')
  } finally {
    isProcessing.value = false
  }
}
</script>

<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl min-h-[70vh]">
    
    <!-- Progress Bar -->
    <div class="flex justify-center items-center mb-12 mt-4">
      <RouterLink to="/checkout" class="flex flex-col items-center relative group cursor-pointer hover:opacity-80 transition">
        <div class="w-10 h-10 rounded-full bg-[#3771C8] text-white flex items-center justify-center font-bold text-sm z-10 ring-4 ring-white">1</div>
        <div class="text-[#3771C8] font-bold text-xs absolute top-12 mt-1">Delivery</div>
      </RouterLink>
      <div class="h-1 w-32 md:w-64 bg-[#3771C8] -mx-1 z-0"></div>
      <div class="h-1 w-32 md:w-64 bg-[#3771C8] -mx-1 z-0"></div>
      <div class="flex flex-col items-center relative">
        <div class="w-10 h-10 rounded-full bg-[#3771C8] text-white flex items-center justify-center font-bold text-sm z-10 ring-4 ring-white">2</div>
        <div class="text-[#3771C8] font-bold text-xs absolute top-12 mt-1">payment</div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#3771C8]"></div>
    </div>

    <div v-else-if="order" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Payment Methods & Promo -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Payment Method Block -->
        <div class="bg-white border border-gray-300 rounded-2xl p-6 md:p-8">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Payment Method</h2>
          <p class="text-sm font-medium text-gray-700 mb-4">You will select your preferred payment method on the next secure page.</p>
          
          <div class="space-y-4">
            <!-- Xendit Info Option -->
            <div class="flex items-center justify-between p-4 border border-[#3771C8] bg-blue-50/30 rounded-xl">
              <div class="flex items-center">
                <div class="w-4 h-4 rounded-full bg-[#3771C8] flex items-center justify-center border-2 border-white shadow-sm ring-1 ring-[#3771C8]"></div>
                <div class="ml-3">
                  <p class="font-bold text-gray-900">Xendit Payment Gateway</p>
                  <p class="text-xs text-gray-500 mt-0.5">Virtual Account, QRIS, E-Wallet, Retail Outlet</p>
                </div>
              </div>
              <div class="hidden sm:flex gap-2 text-[#3771C8] opacity-70">
                <CreditCard class="w-6 h-6" />
                <QrCode class="w-6 h-6" />
              </div>
            </div>
          </div>
        </div>

        <!-- Promo Code Block -->
        <div class="bg-white border border-gray-300 rounded-2xl p-6 md:p-8">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Promo Code</h2>
          <p class="text-sm font-medium text-gray-700 mb-4">Enter your promo code</p>
          
          <div class="flex gap-4">
            <input v-model="promoCode" type="text" placeholder="your promo code" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#3771C8] focus:border-[#3771C8] text-sm" />
            <button class="px-6 py-2.5 bg-[#3771C8] text-white font-bold rounded-lg hover:opacity-90 transition inline-block">
              Send
            </button>
          </div>
        </div>

      </div>

      <!-- Right Column: Summary -->
      <div class="lg:col-span-1">
        <div class="bg-white border border-gray-300 rounded-2xl p-6 md:p-8 sticky top-24">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Shopping Summary</h2>
          
          <div v-if="order.items && order.items.length > 0" class="max-h-64 overflow-y-auto pr-2 mb-6 space-y-4">
            <div v-for="item in order.items" :key="item.id" class="flex gap-5 p-4 border border-gray-100 rounded-xl bg-gray-50/50">
              <div class="w-24 h-24 bg-white rounded-lg flex-shrink-0 flex items-center justify-center p-2 border border-gray-200">
                <img 
                  :src="item.product?.images?.[0]?.image_url ? `http://localhost:8000/storage/${item.product.images[0].image_url}` : 'https://placehold.co/100x100?text=No+Image'" 
                  :alt="item.product?.name" 
                  class="w-full h-full object-contain mix-blend-multiply"
                />
              </div>
              <div class="flex-1 flex flex-col justify-center">
                <p class="text-[11px] font-bold text-[#3771C8] uppercase tracking-wider mb-1">{{ item.product?.brand?.name || 'Brand' }}</p>
                <h3 class="text-sm font-bold text-gray-900 leading-normal mb-3">{{ item.product?.name || 'Unknown Product' }}</h3>
                <p class="text-xs text-gray-500 mb-1">Quantity: {{ item.quantity }}</p>
                <p class="text-xs text-gray-500">Size: {{ item.variant?.size || 'Default' }}</p>
              </div>
            </div>
          </div>
          
          <div class="border-t border-gray-200 pt-6 mb-6">
            <div class="flex justify-between text-gray-600 font-medium mb-3">
              <span>Subtotal</span>
              <span>{{ formatPrice(order.total_amount) }}</span>
            </div>
            <div class="flex justify-between text-gray-600 font-medium">
              <span>Delivery</span>
              <span class="text-gray-900">Free</span>
            </div>
          </div>
          
          <div class="border-t border-gray-200 pt-4 mb-8">
            <div class="flex justify-between items-center">
              <span class="font-bold text-gray-900">Order Amount</span>
              <span class="font-extrabold text-xl text-gray-900">{{ formatPrice(order.total_amount) }}</span>
            </div>
          </div>
          
          <button 
            @click="processPayment" 
            :disabled="isProcessing"
            class="w-full bg-[#3771C8] text-white font-bold py-4 rounded-xl hover:opacity-90 hover:shadow-lg transition flex justify-center items-center group disabled:opacity-50"
          >
            <div v-if="isProcessing" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            Order
          </button>
        </div>
      </div>
      
    </div>
  </div>
</template>
