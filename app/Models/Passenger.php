<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    protected $guarded = [];

    // Relasi ke Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // --- TAMBAHKAN INI (YANG HILANG TADI) ---
    // Agar tiket bisa menampilkan nama kategori (Dewasa/Anak)
    public function ticket_category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }
}