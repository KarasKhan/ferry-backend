<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_port_id')->constrained('ports')->cascadeOnDelete();
            $table->foreignId('destination_port_id')->constrained('ports')->cascadeOnDelete();
            $table->integer('duration_minutes')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('routes'); }
};