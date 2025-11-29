<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $guarded = [];

    public function ship()
    {
        return $this->belongsTo(Ship::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}