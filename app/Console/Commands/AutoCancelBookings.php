<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoCancelBookings extends Command
{
    // Nama perintah untuk memanggil robot ini
    protected $signature = 'bookings:cleanup';

    // Deskripsi
    protected $description = 'Batalkan booking pending yang kadaluwarsa (> 60 menit) dan kembalikan kuota.';

    public function handle()
    {
        // 1. Tentukan batas waktu (misal: 60 menit yang lalu)
        $timeLimit = Carbon::now()->subMinutes(60);

        $this->info("Mencari booking pending sebelum: " . $timeLimit->toDateTimeString());

        // 2. Cari Booking yang 'pending' DAN sudah tua
        $expiredBookings = Booking::where('payment_status', 'pending')
            ->where('created_at', '<', $timeLimit)
            ->get();

        if ($expiredBookings->count() === 0) {
            $this->info("Tidak ada booking kadaluwarsa.");
            return;
        }

        $this->info("Ditemukan {$expiredBookings->count()} booking kadaluwarsa. Memproses...");

        foreach ($expiredBookings as $booking) {
            try {
                DB::transaction(function () use ($booking) {
                    
                    // A. Ubah Status jadi Cancelled
                    $booking->update(['payment_status' => 'cancelled']);

                    // B. Kembalikan Kuota (Refund)
                    // Hitung jumlah penumpang di booking ini
                    $totalPassengers = $booking->passengers()->count();
                    
                    // Kembalikan ke jadwal
                    $schedule = Schedule::find($booking->schedule_id);
                    if ($schedule) {
                        $schedule->increment('quota_passenger_left', $totalPassengers);
                    }

                    $this->info("Booking {$booking->booking_code} dibatalkan. Kuota dikembalikan: +{$totalPassengers}");
                });

            } catch (\Exception $e) {
                $this->error("Gagal memproses {$booking->booking_code}: " . $e->getMessage());
            }
        }

        $this->info("Selesai membersihkan booking.");
    }
}