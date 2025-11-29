<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\BookingController; // <--- PINDAHKAN KE SINI (PALING ATAS)

// --- PUBLIC ROUTES (Bisa diakses tanpa login) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// JADWAL & PELABUHAN (Wajib Public, biar orang bisa cari tiket dulu sebelum login)
Route::get('/ports', [ScheduleController::class, 'getPorts']);
Route::get('/schedules', [ScheduleController::class, 'search']);

// --- PROTECTED ROUTES (Harus Login / Punya Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Cek Profile Sendiri
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Transaksi (Booking)
    Route::post('/bookings', [BookingController::class, 'store']); // Beli Tiket
    Route::get('/my-bookings', [BookingController::class, 'index']); // Lihat History
    Route::get('/bookings/{code}/download', [BookingController::class, 'downloadTicket']);
});