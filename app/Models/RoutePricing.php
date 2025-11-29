<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePricing extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }
}