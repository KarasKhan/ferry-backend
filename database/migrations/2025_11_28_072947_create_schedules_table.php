<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ship_id')->constrained('ships')->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->enum('status', ['open', 'closed', 'delayed', 'completed'])->default('open');
            $table->integer('quota_passenger_left');
            $table->integer('quota_vehicle_left');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('schedules'); }
};