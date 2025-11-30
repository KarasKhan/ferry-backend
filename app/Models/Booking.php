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

        // 1. Logic Kode Booking Otomatis (SUDAH ADA SEBELUMNYA)
        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BOOK-' . strtoupper(Str::random(8));
            }
        });

        // 2. Logic Refund Kuota saat DIHAPUS (INI BARU)
        static::deleting(function ($booking) {
            // Cek: Jangan refund kalau statusnya sudah 'cancelled' 
            // (karena yang cancelled biasanya kuotanya sudah dikembalikan oleh scheduler)
            if ($booking->payment_status !== 'cancelled') {
                
                // Ambil jadwal terkait
                $schedule = $booking->schedule;
                
                // Hitung jumlah penumpang di booking ini
                $paxCount = $booking->passengers()->count();

                if ($schedule && $paxCount > 0) {
                    // Kembalikan Kuota
                    $schedule->increment('quota_passenger_left', $paxCount);
                }
            }
        });
    }

    // ... (Relasi-relasi di bawah tetap sama) ...
    public function user() { return $this->belongsTo(User::class); }
    public function schedule() { return $this->belongsTo(Schedule::class); }
    public function passengers() { return $this->hasMany(Passenger::class); }
    
    public static function getByBatch($batchId) {
        return static::where('batch_id', $batchId)->get();
    }
}