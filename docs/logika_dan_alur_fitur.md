# Panduan Logika & Alur Fitur Lengkap Solevia V3 🚀
*(Dokumen Persiapan Pertanyaan Guru / Presentasi)*

Dokumen ini menjelaskan secara mendalam "bagaimana cara kerja" dari seluruh fitur utama di sistem *Headless E-commerce* Solevia V3. Jika Anda ditanya "Bagaimana cara stok berkurang?" atau "Di mana letak kode login?", semua jawabannya ada di sini.

---

## 1. Fitur Autentikasi (Login, Register & Session)

**Konsep:** Aplikasi ini menggunakan **Token-based Authentication (Laravel Sanctum)** karena Backend dan Frontend dipisah total. Tidak ada session cookie tradisional.

### A. Alur Register & Login
- **Frontend**: Pengguna mengisi formulir di `RegisterView.vue` atau `LoginView.vue`.
- **Axios**: Memanggil API `POST /api/register` atau `POST /api/login`.
- **Backend (`AuthController.php`)**:
  - Untuk login, sistem mengecek apakah email ada dan password cocok (menggunakan fungsi Hash Laravel).
  - Jika benar, Backend menerbitkan sebuah **Token Unik** rahasia (contoh: `1|abCdEfG...`) menggunakan Sanctum.
- **Penyimpanan State (Frontend)**:
  - Token ini dikembalikan ke Vue, lalu Vue menyimpannya di dua tempat: 
    1. **Pinia (`stores/auth.ts`)**: Agar token bisa dipakai langsung secara cepat di memori komputer.
    2. **`localStorage` Browser**: Agar jika pengguna me-refresh atau menutup browser (Google Chrome), mereka tidak perlu login ulang.

### B. Logic Keamanan "Gembok" (Navigation Guard & Interceptor)
- **Bagaimana Backend tahu siapa yang request?**
  Setiap kali Frontend mengambil data rahasia (seperti isi keranjang), Axios di file `src/api/axios.ts` secara diam-diam akan **menyelipkan Token** tersebut di bagian `Header` HTTP (`Authorization: Bearer <token>`).
- **Bagaimana Frontend mencegah tamu masuk ke halaman Checkout?**
  Di `router/index.ts`, terdapat aturan *Navigation Guard*. Vue Router akan mencegat setiap perindahan URL. Jika halaman itu dilabeli `requiresAuth: true` tetapi `localStorage` tokennya kosong, pengguna langsung "ditendang" ke halaman `/login`.

---

## 2. Fitur Katalog Produk & Pencarian

### A. Alur Pemanggilan
- Saat `HomeView.vue` atau `CategoryView.vue` dimuat, Frontend memanggil `ProductService.getProducts()`.
- **Backend (`ProductController.php @ index`)**:
  - Menarik data `Product` beserta relasinya (`category`, `brand`, `images`, `variants`).
  - **Pencarian & Filter**: Backend mengecek apakah URL memiliki kata kunci (misal `?search=sepatu&category=sneakers`). Jika ada, Laravel Eloquent akan menambahkan perintah SQL `WHERE` secara dinamis.
  - Data diformat menggunakan `ProductResource` sebelum dikembalikan.
- **Tampilan Otomatis**: Frontend menerima JSON bersih, memasukkannya ke dalam array, dan *ProductCard* langsung merender daftar sepatu di layar.

---

## 3. Fitur Keranjang Belanja (Cart)

**Konsep:** Keranjang bersifat permanen di database, bukan hanya di memori browser. Artinya, pengguna bisa masuk keranjang di HP dan melihatnya juga di Laptop (karena tersimpan di MySQL).

### A. Menambahkan Barang
- Pengguna wajib memilih *Size* (Varian) terlebih dahulu di halaman Produk.
- **Frontend**: Mengirim `variant_id` dan `quantity` ke `POST /api/cart/items`.
- **Backend (`CartController.php @ addItem`)**:
  1. Mengecek apakah pengguna ini sudah punya "Tabel Keranjang" (`Cart`). Jika belum, buatkan ID keranjang baru.
  2. Mengecek apakah sepatu berukuran tersebut (`variant_id`) **sudah ada** di dalam keranjangnya.
  3. **Jika sudah ada**: Jangan buat baris baru, cukup **tambahkan jumlahnya** (`quantity = quantity + input`).
  4. **Jika belum ada**: Buat baris baru di tabel `cart_items`.

### B. Hitung Harga Otomatis (Pinia)
- Di Frontend (`stores/cart.ts`), terdapat variabel *computed*. Vue secara otomatis mengalikan jumlah barang dengan harga `discount_price` secara *real-time* setiap kali pengguna menekan tombol "+" atau "-", tanpa perlu repot menghitung ulang secara manual.

---

## 4. Fitur Checkout & Pengurangan Stok (⚠️ Sering ditanyakan Guru)

**Alur Puncak:** Ini adalah fungsi paling krusial di e-commerce. Semua terjadi dalam 1 klik di `OrderController.php @ checkout`.

### Logic / Urutan Eksekusi Backend:
1. **Validasi**: Pastikan alamat terisi lengkap dan keranjang tidak kosong.
2. **Kalkulasi Server-side**: Jangan pernah percaya hitungan Total Harga dari Frontend (rawan di-hack). Backend melakukan *looping* menghitung ulang `Total Harga = Harga Asli Produk × Kuantitas` untuk setiap item di keranjang.
3. **Membuat Induk Pesanan**: Record baru dibuat di tabel `orders` dengan `status = pending`.
4. **Memecah Item**: Memindahkan barang dari keranjang (`cart_items`) ke tabel pesanan (`order_items`).
5. **PENGURANGAN STOK OTO-MATIS**: 
   - Di dalam *looping* pemindahan item, sistem memanggil perintah: 
   - `$item->variant->decrement('stock', $item->quantity);`
   - Ini secara otomatis mengurangi stok Varian sepatu di database sebanyak kuantitas yang dibeli.
6. **Membuat Alamat & Status Bayar**: Sistem menyimpan ke tabel `shippings` dan `payments` (status `pending`).
7. **Pembersihan**: Semua barang di keranjang dihapus (`$cart->items()->delete()`).

---

## 5. Fitur Batal Pesanan (Cancel) & Pengembalian Stok

Jika pelanggan batal membayar, stok tidak boleh hangus.

- **Alur & Syarat Pembatalan**: 
  - Tombol "Cancel Order" di Frontend hanya muncul jika pesanan masih berstatus `pending`.
  - Jika ditekan, Vue memanggil API `PUT /api/orders/{id}/cancel`.
- **Backend (`OrderController.php @ cancel`)**:
  - **Pengembalian Stok (Restock)**: Sistem membaca kembali `order_items`, lalu mengeksekusi kebalikan dari pengurangan stok:
  - `$item->variant->increment('stock', $item->quantity);`
  - Stok sepatu kembali utuh! Status order diubah ke `cancelled`.

---

## 6. Fitur Pembayaran (Payment Gateway Xendit)

Sistem menggunakan pihak ketiga (Xendit) untuk menerima pembayaran (Transfer Bank, QRIS, dll).

### A. Alur Integrasi:
1. Pengguna klik "Pay Now" atau "Order" di halaman Payment Vue.
2. Frontend menembak `POST /api/orders/{id}/pay`.
3. **Backend (`OrderController.php @ createInvoice`)**:
   - Backend menghubungi server Xendit via HTTP (dengan menyertakan `XENDIT_SECRET_KEY`).
   - Data yang dikirim: `external_id` (nomor pesanan), `amount` (total harga), `payer_email`, dan `success_redirect_url` (URL untuk kembali ke web kita setelah bayar).
   - Server Xendit membalas dengan memberikan `invoice_url` (URL halaman khusus dari Xendit tempat pelanggan akan memasukkan PIN ATM/QRIS).
4. **Frontend**: Menerima `invoice_url`, lalu secara otomatis mengalihkan (redirect) tab browser ke URL tersebut (`window.location.href`).
5. *Catatan Simulasi Lokal*: Karena ini proyek sekolah dan webhook lokal belum terhubung sempurna, backend Anda diprogram untuk me-set status order menjadi `processing` (dibayar) seketika invoice dibuat, agar alurnya terlihat berhasil dari kacamata penguji.

---

## 7. Fitur Wishlist (Toggle Logic)

**Konsep:** Alih-alih membuat dua API terpisah untuk "Tambah Wishlist" dan "Hapus Wishlist", sistem ini menggunakan satu API saja: `POST /api/wishlists/toggle`.

- **Cara Kerjanya (`WishlistController.php @ toggle`)**:
  1. Cek database: Apakah `product_id` X sudah dimiliki oleh user A?
  2. **Jika Ketemu (Sudah ada)**: Maka hapus dari tabel (`delete()`). (Pesan: Dihapus dari wishlist).
  3. **Jika Tidak Ketemu (Belum ada)**: Maka buatkan di tabel (`create()`). (Pesan: Ditambahkan ke wishlist).
  - Teknik ini sangat menghemat penulisan kode di Frontend dan Backend!

---

## 8. Peta Letak File / Konfigurasi Penting

Jika guru Anda meminta Anda membuka file konfigurasinya, ingatlah peta ini:

- **Sambungan Database (Nama DB, Password MySQL)**
  - Letak: `/backend/.env`
- **Kunci Rahasia API Xendit (Secret Key)**
  - Letak: `/backend/.env`
- **Rumah dari Segala URL Backend (Routing API)**
  - Letak: `/backend/routes/api.php`
- **Rumah dari Segala Logika Transaksi (Pengurangan Stok, Hitung Harga, Xendit)**
  - Letak: `/backend/app/Http/Controllers/Api/OrderController.php` dan `CartController.php`
- **Tempat Menyimpan Token di Frontend (Memori Global Pinia)**
  - Letak: `/frontend/src/stores/auth.ts`
- **Tempat Konfigurasi Axios Frontend (Base URL Backend & Penyisipan Token)**
  - Letak: `/frontend/src/api/axios.ts`
- **Proteksi Halaman Vue Router (Navigation Guards)**
  - Letak: `/frontend/src/router/index.ts`
