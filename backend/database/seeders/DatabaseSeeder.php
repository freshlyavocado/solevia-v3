<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== ADMIN USER ====================
        User::firstOrCreate(
            ['email' => 'admin@solevia.com'],
            [
                'name' => 'Admin Solevia',
                'password' => Hash::make('12345'),
            ]
        );

        // ==================== CATEGORIES ====================
        $categories = [
            ['name' => 'Sneakers', 'description' => 'Sepatu casual untuk sehari-hari'],
            ['name' => 'Running', 'description' => 'Sepatu untuk berlari dan olahraga'],
            ['name' => 'Formal', 'description' => 'Sepatu formal untuk acara resmi'],
            ['name' => 'Boots', 'description' => 'Sepatu boots untuk gaya dan outdoor'],
            ['name' => 'Sandals', 'description' => 'Sandal dan slip-on'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // ==================== BRANDS ====================
        $brands = [
            ['name' => 'Nike', 'description' => 'Just Do It'],
            ['name' => 'Adidas', 'description' => 'Impossible Is Nothing'],
            ['name' => 'Puma', 'description' => 'Forever Faster'],
            ['name' => 'New Balance', 'description' => 'Fearlessly Independent'],
            ['name' => 'Converse', 'description' => 'Shoes Are Boring. Wear Sneakers'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['name' => $brand['name']], $brand);
        }

        // ==================== PRODUCTS ====================
        $products = [
            [
                'name' => 'Nike Air Max 90',
                'slug' => 'nike-air-max-90',
                'description' => 'Sepatu ikonik dengan bantalan Air Max yang nyaman untuk sehari-hari.',
                'price' => 2899000,
                'discount_percentage' => 15,
                'category_name' => 'Sneakers',
                'brand_name' => 'Nike',
                'sizes' => ['39', '40', '41', '42', '43', '44'],
            ],
            [
                'name' => 'Adidas Ultraboost 23',
                'slug' => 'adidas-ultraboost-23',
                'description' => 'Sepatu running premium dengan teknologi Boost untuk responsivitas maksimal.',
                'price' => 3599000,
                'discount_percentage' => 10,
                'category_name' => 'Running',
                'brand_name' => 'Adidas',
                'sizes' => ['40', '41', '42', '43', '44'],
            ],
        ];

        foreach ($products as $prod) {
            $category = Category::where('name', $prod['category_name'])->first();
            $brand = Brand::where('name', $prod['brand_name'])->first();

            $product = Product::firstOrCreate(
                ['slug' => $prod['slug']],
                [
                    'name' => $prod['name'],
                    'description' => $prod['description'],
                    'price' => $prod['price'],
                    'discount_percentage' => $prod['discount_percentage'],
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                ]
            );

            // Buat variants berdasarkan ukuran
            foreach ($prod['sizes'] as $size) {
                ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'size' => $size],
                    ['stock' => rand(5, 15)]
                );
            }
        }
    }
}
