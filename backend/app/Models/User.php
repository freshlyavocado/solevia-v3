<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     * Ini untuk keamanan — hanya kolom ini yang bisa diisi via create() / update().
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Kolom yang disembunyikan saat model di-convert ke array/JSON.
     * Password dan token tidak boleh terexpose ke frontend.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting otomatis — Laravel akan otomatis convert tipe data.
     * 'hashed' = password otomatis di-hash saat disimpan.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * User punya satu Cart (keranjang belanja).
     * Relasi: users.id → carts.user_id
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * User punya banyak Order (pesanan).
     * Relasi: users.id → orders.user_id
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * User punya banyak Wishlist.
     * Relasi: users.id → wishlists.user_id
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
