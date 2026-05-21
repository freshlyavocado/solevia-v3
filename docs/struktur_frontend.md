# 🎨 Struktur Frontend (Vue 3 + Vite)

Frontend adalah "Etalase Toko" aplikasi Solevia. Ini adalah apa yang pelanggan Anda lihat, sentuh, dan gunakan. Dibangun menggunakan **Vue 3**, **TypeScript**, dan **Pinia** untuk manajemen datanya.

Agar kode tidak menumpuk menjadi satu dan sulit dibaca, kita membaginya ke dalam folder-folder dengan tugas yang sangat spesifik:

## 1. `src/pages/` 📄
Ini adalah halaman-halaman utama (layar) yang akan dilihat oleh pengguna. Satu file di sini biasanya mewakili satu URL (misalnya `/cart` atau `/checkout`).
- **Contoh:** `HomeView.vue`, `CartView.vue`, `PaymentView.vue`.

## 2. `src/components/` 🧩
Ini adalah "Pecahan Lego". Bagian-bagian antarmuka (UI) kecil yang bisa dipakai berulang kali di berbagai halaman yang berbeda. 
- **Contoh:** `ProductCard.vue` (kotak sepatu yang muncul di halaman *Home* maupun *Category*).

## 3. `src/layouts/` 🖼️
Ini adalah "Bingkai Halaman". Daripada menulis *Header* (Navbar) dan *Footer* di setiap file halaman, kita menaruhnya di sini.
- **Contoh:** `DefaultLayout.vue` memiliki Navbar di atas dan halaman yang sedang aktif di bagian bawahnya.

## 4. `src/router/` 🚦
Ini adalah "Polisi Lalu Lintas". Tugasnya mengatur jika pengguna mengetik URL `/login`, maka file `LoginView.vue` yang harus ditampilkan. Ia juga bertugas mengusir (me-redirect) pengguna yang mencoba masuk ke `/checkout` padahal belum login.

## 5. `src/services/` 📞
Ini adalah sang "Kurir Telepon". Di sinilah kita menaruh fungsi-fungsi untuk menelepon backend (melalui API). Tujuannya agar halaman (`pages`) Anda bersih dari kode *fetch/axios* yang panjang.
- **Contoh:** `AuthService.ts`, `CartService.ts`, `OrderService.ts`. Di sinilah fungsi `createInvoice()` Xendit dipanggil.

## 6. `src/stores/` 🧠 (Pinia)
Ini adalah "Memori Otak Jangka Pendek". Jika Anda menambah barang ke keranjang, Anda tentu ingin angka keranjang di pojok kanan atas (Navbar) langsung ikut berubah, bukan? *Store* menyimpan data secara terpusat (global) agar bisa dibaca dan diubah oleh *semua halaman* sekaligus.
- **Contoh:** `auth.ts` (Menyimpan token login dan profil), `cart.ts` (Menyimpan daftar belanjaan).

## 7. `src/types/` 🏷️ (TypeScript)
Ini adalah "Label Aturan". Karena kita memakai TypeScript, kita mendefinisikan bentuk data di sini. Misalnya, aturan bahwa "Sebuah Produk HARUS memiliki `name`, `price`, dan `images`". Ini sangat membantu mencegah *error* "Unknown Product" sebelum aplikasi dijalankan.
- **Contoh:** `product.ts`, `order.ts`.

---
💡 **Ringkasan Alur Kerja Frontend:**
Pengguna menekan tombol "Add to Cart" di **`ProductCard (Component)`** ➡️ Ia memicu fungsi keranjang di **`Store`** ➡️ Store meminta **`Service`** untuk menelepon Backend ➡️ Backend membalas "Sukses!" ➡️ Store memperbarui jumlah keranjang, dan semua komponen di layar ikut ter-update seketika!
