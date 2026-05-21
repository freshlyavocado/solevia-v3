<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_percentage',
        'category_id',
        'brand_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Hitung harga setelah diskon.
     * Contoh: price=100000, discount_percentage=20 → return 80000
     */
    public function getDiscountPriceAttribute()
    {
        if ($this->discount_percentage) {
            return $this->price * (1 - $this->discount_percentage / 100);
        }
        return $this->price;
    }
}
