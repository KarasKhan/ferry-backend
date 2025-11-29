<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BOOK-' . strtoupper(Str::random(8));
            }
        });
    }

    // --- RELASI-RELASI YANG SUDAH ADA ---
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    // --- TEMPEL FUNGSI BARU DI SINI (SEBELUM KURUNG TUTUP TERAKHIR) ---
    public static function getByBatch($batchId)
    {
        // Gunakan 'static::' lebih aman daripada 'self::' di Laravel
        return static::where('batch_id', $batchId)->get();
    }

} // <--- Pastikan fungsi ada DI ATAS kurung ini