<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthController
 * 
 * Mengelola semua proses autentikasi pengguna menggunakan Laravel Sanctum.
 * Mengontrol alur registrasi akun baru, login, logout, serta pengambilan dan pembaruan profil pengguna.
 */
class AuthController extends Controller
{
    /**
     * REGISTER — Daftarkan user baru.
     *
     * Alur:
     * 1. Validasi input (nama, email, password)
     * 2. Buat user baru di database
     * 3. Buat token API (untuk autentikasi selanjutnya)
     * 4. Return data user + token
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed', // harus ada field password_confirmation
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // otomatis di-hash karena $casts di model
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * LOGIN — Autentikasi user yang sudah terdaftar.
     *
     * Alur:
     * 1. Validasi input
     * 2. Cek apakah email & password cocok
     * 3. Kalau cocok, buat token baru
     * 4. Return data user + token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek apakah user ditemukan & password benar
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * LOGOUT — Hapus token user yang sedang login.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * PROFILE — Ambil data user yang sedang login.
     */
    public function profile(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * UPDATE PROFILE — Update data profil user.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $user = $request->user();
        $user->update($request->only(['name']));

        return new UserResource($user);
    }
}
