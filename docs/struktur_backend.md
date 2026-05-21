# 🧠 Struktur Backend (Laravel 13)

Backend adalah "dapur" dari aplikasi Solevia. Di sinilah semua pesanan diproses, data pengguna disimpan dengan aman, dan pembayaran dihubungkan ke Xendit. Backend ini dibangun menggunakan kerangka kerja (framework) **Laravel 13**.

Berikut adalah folder-folder terpenting yang perlu Anda pahami:

## 1. `app/Http/Controllers/Api/` 🎮
Ini adalah sang "Manajer". Saat frontend meminta sesuatu (misalnya: "Tolong tampilkan daftar produk!"), *Controller* yang bertugas mencarikan datanya dan mengirimkannya kembali.
- **Contoh:** `OrderController.php` bertugas membuat pesanan baru, menghitung total, dan membuat tagihan Xendit.

## 2. `app/Http/Resources/` 🎁
Ini adalah sang "Pembungkus Kado". Data mentah dari database biasanya berantakan. *Resource* bertugas membungkus data tersebut menjadi format JSON yang rapi dan cantik sebelum dikirim ke Frontend.
- **Contoh:** `ProductResource.php` memastikan frontend mendapatkan `name`, `price`, dan `discount_price` sesuai format yang disepakati.

## 3. `app/Models/` 🧬
Ini adalah "DNA" aplikasi Anda. *Model* menghubungkan kode PHP Anda langsung dengan tabel di database MySQL. Di sini juga kita mengatur hubungan (relasi) antar data, seperti "Satu *Order* memiliki banyak *OrderItem*".
- **Contoh:** `User.php`, `Product.php`, `Order.php`.

## 4. `routes/api.php` 🗺️
Ini adalah "Peta Jalan" atau "Buku Menu". Semua tautan (URL) API yang bisa diakses oleh frontend harus didaftarkan di sini.
- **Contoh:** `Route::get('/products')` (Untuk melihat produk), `Route::post('/login')` (Untuk masuk akun).

## 5. `database/migrations/` 🏗️
Ini adalah "Cetak Biru Bangunan". Berisi skrip PHP yang akan memerintahkan MySQL untuk membuat kolom dan tabel database. Jika Anda ingin menambah kolom baru, Anda harus membuat *migration*.
- **Contoh:** `2026_xx_xx_create_orders_table.php` yang berisi kolom `status`, `total_amount`, dll.

## 6. `database/seeders/` 🌱
Ini adalah "Penabur Benih". Digunakan untuk mengisi tabel database Anda dengan data bohongan (dummy) secara otomatis agar Anda tidak perlu menginputnya satu per satu saat baru menginstal proyek.
- **Contoh:** `DatabaseSeeder.php` yang memasukkan akun `admin@solevia.com` dan sepatu Nike/Adidas pertama.

---
💡 **Ringkasan Alur Kerja Backend:**
Frontend meminta data ke URL di **`routes/api.php`** ➡️ Router mengarahkan ke **`Controller`** ➡️ Controller mengambil data di MySQL melalui **`Model`** ➡️ Data dibungkus rapi oleh **`Resource`** ➡️ Dikirim kembali ke Frontend!
