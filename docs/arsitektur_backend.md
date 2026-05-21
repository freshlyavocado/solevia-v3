# Arsitektur & Alur Backend Solevia V3

## Tech Stack
- **Framework**: Laravel 13
- **Admin Panel**: Filament v5
- **Auth**: Laravel Sanctum (Token-based)
- **Database**: MySQL (`solevia_v3`)

---

## Struktur Folder Backend

```
app/
├── Filament/Resources/              # Admin Panel (CRUD via Filament v5)
│   ├── Products/                    # Kelola produk
│   │   ├── ProductResource.php      # Konfigurasi resource
│   │   ├── Schemas/ProductForm.php  # Form input produk
│   │   ├── Tables/ProductsTable.php # Tabel list produk
│   │   └── Pages/                   # Halaman CRUD
│   ├── Categories/                  # Kelola kategori
│   ├── Brands/                      # Kelola brand/merek
│   ├── Orders/                      # Kelola pesanan
│   └── Users/                       # Kelola user/customer
│
├── Http/
│   ├── Controllers/Api/             # Logic bisnis API
│   │   ├── AuthController.php       # Register, Login, Logout, Profile
│   │   ├── ProductController.php    # Lihat produk (index, show)
│   │   ├── CategoryController.php   # Lihat kategori
│   │   ├── BrandController.php      # Lihat brand
│   │   ├── CartController.php       # Keranjang belanja (CRUD items)
│   │   ├── OrderController.php      # Checkout, riwayat pesanan
│   │   └── WishlistController.php   # Produk favorit (toggle)
│   │
│   └── Resources/                   # Transformasi data → JSON
│       ├── UserResource.php
│       ├── ProductResource.php
│       ├── ProductImageResource.php
│       ├── ProductVariantResource.php
│       ├── CategoryResource.php
│       ├── BrandResource.php
│       ├── CartResource.php
│       ├── CartItemResource.php
│       ├── OrderResource.php
│       ├── OrderItemResource.php
│       ├── PaymentResource.php
│       ├── ShippingResource.php
│       └── WishlistResource.php
│
├── Models/                          # Representasi tabel database
│   ├── User.php                     # + HasApiTokens (Sanctum)
│   ├── Product.php                  # belongsTo Category & Brand
│   ├── ProductImage.php             # belongsTo Product
│   ├── ProductVariant.php           # belongsTo Product
│   ├── Category.php                 # hasMany Products
│   ├── Brand.php                    # hasMany Products
│   ├── Cart.php                     # belongsTo User, hasMany Items
│   ├── CartItem.php                 # belongsTo Cart & Product
│   ├── Order.php                    # hasMany Items, hasOne Payment
│   ├── OrderItem.php                # belongsTo Order & Product
│   ├── Payment.php                  # belongsTo Order
│   ├── Shipping.php                 # belongsTo Order
│   └── Wishlist.php                 # belongsTo User & Product
│
└── Providers/

database/migrations/                 # Blueprint tabel database (14 tabel)
routes/api.php                       # Endpoint API (18 endpoint)
```

---

## Alur Request API

```
Frontend → Route (api.php) → Controller → Model → Database
                                              ↓
Frontend ← JSON Response ← Resource ← Controller
```

### Contoh: GET /api/products
1. Route menangkap request → diarahkan ke `ProductController@index`
2. Controller memanggil `Product::with(['category', 'brand'])->paginate(15)`
3. Model menjalankan query ke database
4. Controller membungkus hasil dengan `ProductResource::collection()`
5. Resource memformat data → hanya kirim field yang diperlukan
6. Frontend menerima JSON bersih

---

## Autentikasi (Sanctum)

### Alur Login:
1. User kirim email + password ke `POST /api/login`
2. Server validasi → kalau cocok, generate token
3. Token dikirim ke frontend → disimpan di localStorage
4. Setiap request selanjutnya, frontend kirim token di header:
   ```
   Authorization: Bearer {token}
   ```

### Route Publik vs Protected:
- **Publik**: products, categories, brands, register, login
- **Protected** (`auth:sanctum`): cart, orders, checkout, wishlist, profile

---

## Daftar API Endpoint

### Publik (tanpa login)
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/api/register` | Registrasi user baru |
| POST | `/api/login` | Login user |
| GET | `/api/products` | Daftar produk (+ filter & search) |
| GET | `/api/products/{slug}` | Detail produk |
| GET | `/api/categories` | Daftar kategori |
| GET | `/api/categories/{slug}` | Detail kategori |
| GET | `/api/brands` | Daftar brand |
| GET | `/api/brands/{slug}` | Detail brand |

### Protected (butuh token)
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/api/logout` | Logout |
| GET | `/api/profile` | Lihat profil |
| PUT | `/api/profile` | Update profil |
| GET | `/api/cart` | Lihat keranjang |
| POST | `/api/cart/items` | Tambah item ke keranjang |
| PUT | `/api/cart/items/{id}` | Update quantity item |
| DELETE | `/api/cart/items/{id}` | Hapus item dari keranjang |
| POST | `/api/checkout` | Buat pesanan dari keranjang |
| GET | `/api/orders` | Riwayat pesanan |
| GET | `/api/orders/{id}` | Detail pesanan |
| POST | `/api/orders/{id}/pay` | Bayar tagihan (Xendit) |
| PUT | `/api/orders/{id}/cancel` | Batalkan pesanan |
| GET | `/api/wishlists` | Daftar wishlist |
| POST | `/api/wishlists/toggle` | Tambah/hapus wishlist |

---

## Database Schema (14 Tabel)

| Tabel | Deskripsi | Relasi Utama |
|-------|-----------|-------------|
| users | Data pengguna | hasOne Cart, hasMany Orders |
| categories | Kategori produk | hasMany Products |
| brands | Merek/brand | hasMany Products |
| products | Data produk | belongsTo Category & Brand |
| product_images | Gambar produk | belongsTo Product |
| product_variants | Varian ukuran/warna | belongsTo Product |
| carts | Keranjang belanja | belongsTo User |
| cart_items | Item di keranjang | belongsTo Cart & Product |
| orders | Pesanan | belongsTo User, hasOne Payment |
| order_items | Item di pesanan | belongsTo Order & Product |
| payments | Pembayaran | belongsTo Order |
| shippings | Data pengiriman | belongsTo Order |
| wishlists | Produk favorit | belongsTo User & Product |
| personal_access_tokens | Token Sanctum | - |
