<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser; // <--- IMPORT WAJIB
use Filament\Panel; // <--- IMPORT WAJIB
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Tambahkan "implements FilamentUser" di sini
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- FUNGSI SATPAM (PENENTU SIAPA YANG BOLEH MASUK ADMIN) ---
    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya user dengan role 'admin' yang boleh masuk
        // Jika kamu mau 'agent' juga boleh masuk, ubah jadi:
        // return $this->role === 'admin' || $this->role === 'agent';
        
        return $this->role === 'admin';
    }
}