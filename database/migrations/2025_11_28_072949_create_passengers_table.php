<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories');
            $table->string('name');
            $table->string('identity_number')->nullable(); // NIK/Paspor
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->integer('age')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('passengers'); }
};