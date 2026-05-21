# Arsitektur & Alur Frontend Solevia V3

## Tech Stack
- **Framework**: Vue 3 (Composition API / `<script setup>`)
- **Build Tool**: Vite
- **Routing**: Vue Router
- **State Management**: Pinia
- **HTTP Client**: Axios (dengan Interceptor)
- **Styling**: Tailwind CSS & Lucide Vue Next (Ikon)
- **Bahasa**: TypeScript

---

## Struktur Folder Frontend

```text
src/
├── api/                           # Konfigurasi komunikasi HTTP
│   └── axios.ts                   # Setup Base URL & Injeksi Token Sanctum otomatis
│
├── assets/                        # Berkas statis
│   ├── index.css                  # Konfigurasi Tailwind & CSS Global
│   └── images/                    # Gambar/logo bawaan
│
├── components/                    # UI Blocks (Digunakan Berulang)
│   ├── Navbar.vue                 # Navigasi utama
│   ├── Footer.vue                 # Catatan kaki
│   ├── ProductCard.vue            # Kotak tampilan produk
│   ├── CartSidebar.vue            # Keranjang tipe pop-out (Opsional/Sidebar)
│   ├── CategoryCard.vue           # Kotak kategori
│   └── Banner.vue                 # Spanduk promo
│
├── layouts/                       # Bingkai Tata Letak
│   └── DefaultLayout.vue          # Membungkus halaman dengan Navbar & Footer
│
├── pages/                         # Halaman Utama (Views)
│   ├── HomeView.vue               # Beranda (Banner, Kategori, Produk unggulan)
│   ├── CategoryView.vue           # Produk berdasarkan Kategori
│   ├── BrandView.vue              # Produk berdasarkan Merek
│   ├── ProductDetailView.vue      # Detail spesifik produk (pilih ukuran)
│   ├── CartView.vue               # Keranjang Belanja
│   ├── CheckoutView.vue           # Pengisian alamat pengiriman
│   ├── PaymentView.vue            # Ringkasan pembayaran Xendit
│   ├── OrderDetailView.vue        # Rincian pesanan (Invoice & Status)
│   ├── WishlistView.vue           # Daftar produk favorit
│   ├── ProfileView.vue            # Profil user & Riwayat Pesanan
│   ├── LoginView.vue              # Halaman Masuk
│   └── RegisterView.vue           # Halaman Daftar
│
├── router/                        # Pengaturan Navigasi
│   ├── index.ts                   # Induk Router (Konfigurasi `requiresAuth`)
│   ├── public.ts                  # Daftar rute yang bisa diakses bebas
│   └── customer.ts                # Daftar rute yang wajib login
│
├── services/                      # Logika Pemanggilan API (Abstraksi)
│   ├── AuthService.ts             # Login, Register, Profile
│   ├── CartService.ts             # Tambah/Ubah/Hapus keranjang
│   ├── OrderService.ts            # Checkout & Cek Pesanan
│   ├── ProductService.ts          # Ambil data Katalog
│   └── WishlistService.ts         # Toggle produk ke favorit
│
├── stores/                        # Manajemen State Global (Pinia)
│   ├── auth.ts                    # Menyimpan Status Login & Token
│   └── cart.ts                    # Menyimpan jumlah item keranjang
│
├── types/                         # Definisi TypeScript (Interface)
│   ├── api.ts                     # Standar respons JSON Backend
│   ├── cart.ts                    # Struktur data Cart
│   ├── order.ts                   # Struktur data Pesanan & Shipping
│   ├── product.ts                 # Struktur data Produk, Varian, Gambar
│   └── user.ts                    # Struktur data Pengguna
│
├── App.vue                        # Komponen Akar (Root)
└── main.ts                        # Titik masuk eksekusi (Entry point)
```

---

## Alur Permintaan Data (Data Flow)

Konsep utama di Frontend ini adalah pemisahan antara **Tampilan (Pages/Components)**, **Penyimpanan (Stores)**, dan **Jaringan (Services)**.

```text
Tombol di Klik (Pages) 
        ↓
Memanggil fungsi di Pinia (Stores) / atau fungsi lokal
        ↓
Pinia meminta Axios (Services) untuk memanggil API
        ↓
Axios menempelkan "Token" secara otomatis (Interceptors)
        ↓
[ BACKEND MEMPROSES DATA ]
        ↓
Services mengembalikan data ke Pinia/Pages
        ↓
Tampilan (Vue) otomatis berubah! (Reaktivitas)
```

### Contoh: Menambahkan Barang ke Keranjang
1. Pengguna berada di `ProductDetailView.vue` dan menekan **"Add to Cart"**.
2. Komponen menjalankan fungsi `cartStore.addToCart(variantId)`.
3. `cartStore` memanggil `CartService.addItem()`.
4. `CartService` melakukan `apiClient.post('/cart/items')`.
5. Saat sukses, `cartStore` mengambil ulang data keranjang terbaru (`CartService.getCart()`).
6. Reaktivitas Vue membuat angka notifikasi merah di keranjang (`Navbar.vue`) langsung bertambah tanpa perlu *refresh* halaman.

---

## Manajemen Autentikasi (Navigation Guards)

Agar pengguna yang belum masuk (login) tidak bisa membuka halaman pembayaran, Vue Router dikonfigurasi menggunakan *Navigation Guards*.

Di dalam `router/index.ts`:
```typescript
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Jika rute butuh login (requiresAuth) TAPI token kosong
  if (to.meta.requiresAuth && !authStore.token) {
    next('/login') // Paksa ke halaman login
  } else {
    next() // Silakan lewat
  }
})
```

---

## Daftar Halaman & Rute (Vue Router)

### Publik (Bebas Akses)
| Path (URL) | Komponen (View) | Fungsi |
|------------|-----------------|--------|
| `/` | `HomeView.vue` | Etalase Beranda |
| `/login` | `LoginView.vue` | Masuk Akun |
| `/register` | `RegisterView.vue` | Daftar Akun Baru |
| `/product/:slug` | `ProductDetailView.vue` | Detail Produk Spesifik |
| `/category/:slug` | `CategoryView.vue` | Daftar Produk per Kategori |
| `/brand/:slug` | `BrandView.vue` | Daftar Produk per Merek |

### Protected (Wajib Login)
| Path (URL) | Komponen (View) | Fungsi |
|------------|-----------------|--------|
| `/cart` | `CartView.vue` | Cek Keranjang |
| `/checkout` | `CheckoutView.vue` | Isi alamat pengiriman |
| `/payment/:id` | `PaymentView.vue` | Bayar pesanan (via Xendit) |
| `/profile` | `ProfileView.vue` | Edit akun & Daftar riwayat pesanan |
| `/orders/:id` | `OrderDetailView.vue` | Detail dan resi pengiriman |
| `/wishlist` | `WishlistView.vue` | Daftar sepatu favorit |
