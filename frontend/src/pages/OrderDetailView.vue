<!-- 
  INFO FILE
  Nama: OrderDetailView.vue
  Fungsi: Menampilkan rincian spesifik dari satu pesanan (Invoice, Status Pengiriman, Barang).
-->
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import OrderService from '@/services/OrderService'
import { ArrowLeft } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const orderId = route.params.id as string
const order = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  if (!authStore.token) {
    router.push('/login')
    return
  }

  try {
    const response = await OrderService.getOrderById(Number(orderId))
    order.value = (response as any).data || response
  } catch (error) {
    console.error('Failed to fetch order details', error)
    alert('Order not found.')
    router.push('/profile')
  } finally {
    loading.value = false
  }
})

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date)
}

const cancelOrder = async () => {
  if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) return
  try {
    await OrderService.cancelOrder(Number(orderId))
    alert('Pesanan berhasil dibatalkan.')
    // Refresh data pesanan
    const response = await OrderService.getOrderById(Number(orderId))
    order.value = (response as any).data || response
  } catch (error: any) {
    alert(error.response?.data?.message || 'Gagal membatalkan pesanan.')
  }
}
</script>

<template>
  <div class="container mx-auto px-4 py-12 max-w-4xl min-h-[70vh]">
    <button @click="router.push('/profile')" class="flex items-center text-gray-500 hover:text-[#3771C8] font-bold text-sm mb-6 transition">
      <ArrowLeft class="w-4 h-4 mr-2" /> Back to Profile
    </button>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#3771C8]"></div>
    </div>

    <div v-else-if="order" class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
      <!-- Header -->
      <div class="flex flex-wrap justify-between items-start border-b border-gray-100 pb-6 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 mb-1">Order #{{ order.order_number }}</h1>
          <p class="text-gray-500 text-sm">Placed on {{ formatDate(order.created_at) }}</p>
        </div>
        <div class="mt-4 md:mt-0 text-right">
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider inline-block mb-2"
              :class="{
                'bg-yellow-100 text-yellow-700': order.status === 'pending',
                'bg-indigo-100 text-indigo-700': order.status === 'processing',
                'bg-purple-100 text-purple-700': order.status === 'shipped',
                'bg-blue-100 text-blue-700': order.status === 'completed',
                'bg-red-100 text-red-700': order.status === 'cancelled'
              }"
            >
              {{ order.status }}
            </span>
          <p class="text-[#3771C8] font-extrabold text-xl">{{ formatPrice(order.total_amount) }}</p>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <!-- Shipping Info -->
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
          <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Shipping Information</h3>
          <template v-if="order.shipping">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-500 text-sm w-24 shrink-0">Name</span>
                <span class="font-bold text-gray-800 text-sm text-right">{{ order.shipping.recipient_name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 text-sm w-24 shrink-0">Phone</span>
                <span class="text-gray-800 text-sm text-right">{{ order.shipping.phone_number }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 text-sm w-24 shrink-0">Address</span>
                <span class="text-gray-800 text-sm text-right">{{ order.shipping.address }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 text-sm w-24 shrink-0">Area</span>
                <span class="text-gray-800 text-sm text-right">{{ order.shipping.city }}, {{ order.shipping.province }} {{ order.shipping.postal_code }}</span>
              </div>
            </div>
          </template>
        </div>

        <!-- Payment Info -->
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
          <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Payment Details</h3>
          <template v-if="order.payment">
            <div class="flex justify-between mb-2">
              <span class="text-gray-600 text-sm">Gateway</span>
              <span class="font-bold text-gray-800 uppercase">{{ order.payment.payment_method === 'xendit' ? 'XENDIT SECURE PAYMENT' : order.payment.payment_method }}</span>
            </div>
            <div class="flex justify-between mb-2">
              <span class="text-gray-600 text-sm">Status</span>
              <span class="font-bold text-gray-800 capitalize" :class="order.payment.status === 'paid' ? 'text-green-600' : 'text-yellow-600'">
                {{ order.payment.status }}
              </span>
            </div>
            <div v-if="order.status === 'pending'" class="mt-4 pt-4 border-t border-gray-200 space-y-2">
              <RouterLink :to="`/payment/${order.id}`" class="block text-center w-full bg-[#3771C8] text-white font-bold py-2 rounded-lg hover:opacity-90 transition inline-block text-sm">
                Pay Now
              </RouterLink>
              <button @click="cancelOrder" class="block text-center w-full bg-red-50 text-red-600 font-bold py-2 rounded-lg hover:bg-red-100 transition inline-block text-sm">
                Cancel Order
              </button>
            </div>
          </template>
        </div>
      </div>

      <!-- Items List -->
      <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Order Items</h3>
      <div class="space-y-4">
        <div v-for="item in order.items" :key="item.id" class="flex gap-5 p-4 border border-gray-100 rounded-xl bg-white">
          <div class="w-20 h-20 bg-gray-50 rounded-lg flex-shrink-0 flex items-center justify-center p-2 border border-gray-100">
            <img 
              :src="item.product?.images?.[0]?.image_url ? `http://localhost:8000/storage/${item.product.images[0].image_url}` : 'https://placehold.co/100x100?text=No+Image'" 
              :alt="item.product?.name" 
              class="w-full h-full object-contain mix-blend-multiply"
            />
          </div>
          <div class="flex-1 flex flex-col justify-center">
            <p class="text-[10px] font-bold text-[#3771C8] uppercase tracking-wider mb-1">{{ item.product?.brand?.name || 'Brand' }}</p>
            <h4 class="text-sm font-bold text-gray-900 leading-tight mb-1">{{ item.product?.name || 'Product' }}</h4>
            <p class="text-xs text-gray-500 mb-1">Size: {{ item.variant?.size || '-' }} &bull; Qty: {{ item.quantity }}</p>
          </div>
          <div class="flex items-center justify-end">
            <p class="font-bold text-gray-900">{{ formatPrice(item.item_price * item.quantity) }}</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
