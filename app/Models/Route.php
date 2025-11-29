<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $guarded = [];

    // Relasi ke Pelabuhan Asal
    public function origin()
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    // Relasi ke Pelabuhan Tujuan
    public function destination()
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }

    // Relasi ke Harga (Supaya bisa input harga di form Rute)
    public function pricings()
    {
        return $this->hasMany(RoutePricing::class);
    }
}