<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. REGISTER (Daftar User Baru)
    public function register(Request $request)
    {
        // Validasi Input
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buat User Baru
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'customer', // Default user biasa
            'phone'    => $request->phone,
        ]);

        // Beri Token Akses
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Register berhasil',
            'data'    => $user,
            'token'   => $token,
        ], 201);
    }

    // 2. LOGIN (Masuk)
    public function login(Request $request)
    {
        // Cek apakah email & password cocok
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email atau Password salah'
            ], 401);
        }

        // Ambil data user
        $user = User::where('email', $request->email)->firstOrFail();
        
        // Hapus token lama (opsional, biar bersih)
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login sukses',
            'data'    => $user,
            'token'   => $token,
        ]);
    }

    // 3. LOGOUT (Keluar)
    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil'
        ]);
    }
}