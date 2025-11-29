<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat User Admin Otomatis
        User::firstOrCreate(
            ['email' => 'admin@kapal.com'], // Cek email ini
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Password default
                'role' => 'admin',
                'phone' => '08123456789',
                'email_verified_at' => now(),
            ]
        );

        // Buat User Customer Dummy (Opsional)
        User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Pengguna Contoh',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '08987654321',
            ]
        );
    }
}