<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // <--- Import Log buat CCTV

class Booking extends Model
{
    protected $guarded = [];

    // Gunakan 'booted' bukan 'boot' untuk Laravel Modern
    protected static function booted(): void
    {
        // 1. Auto Generate Code
        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BOOK-' . strtoupper(Str::random(8));
            }
        });

        // 2. Logic Refund Kuota (Versi Lebih Kuat)
        static::deleting(function ($booking) {
            
            // Pasang CCTV: Cek Log nanti di Railway
            Log::info("Mencoba menghapus booking: " . $booking->booking_code);

            // Cek Status: Hanya refund jika belum Cancelled
            if ($booking->payment_status !== 'cancelled') {
                
                // Cari manual jadwalnya (biar pasti ketemu)
                $schedule = Schedule::find($booking->schedule_id);
                
                // Hitung penumpang manual dari database
                $paxCount = Passenger::where('booking_id', $booking->id)->count();

                Log::info("Status OK. Penumpang: $paxCount. Schedule ID: " . $booking->schedule_id);

                if ($schedule && $paxCount > 0) {
                    // Balikin Kuota
                    $schedule->increment('quota_passenger_left', $paxCount);
                    Log::info("Kuota berhasil dikembalikan.");
                } else {
                    Log::warning("Gagal refund: Jadwal tidak ketemu atau penumpang 0.");
                }
            } else {
                Log::info("Skip refund karena status sudah cancelled.");
            }
        });
    }

    // --- Relasi ---
    public function user() { return $this->belongsTo(User::class); }
    public function schedule() { return $this->belongsTo(Schedule::class); }
    public function passengers() { return $this->hasMany(Passenger::class); }
    
    public static function getByBatch($batchId) {
        return static::where('batch_id', $batchId)->get();
    }
}